<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $keyword = $request->search;

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'pengurus', 'bendahara', 'anggota'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // Kolom sensitif diubah secara eksplisit oleh admin.
        $user->role = $data['role'];
        $user->is_active = (bool) $data['is_active'];
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['admin', 'pengurus', 'bendahara', 'anggota'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Lindungi akun admin terakhir yang masih aktif.
        $demotingLastAdmin =
            $user->role === 'admin'
            && $user->is_active
            && ($data['role'] !== 'admin' || ! (bool) $data['is_active']);

        if ($demotingLastAdmin) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->exists();

            if (! $otherActiveAdmins) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'role' => 'Tidak dapat menonaktifkan atau menurunkan role admin terakhir.',
                    ]);
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = (bool) $data['is_active'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        // Jika admin menonaktifkan dirinya sendiri, akhiri sesi.
        if ($request->user()->is($user) && ! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'Akun dinonaktifkan dan Anda telah logout.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if (
            $user->role === 'admin'
            && $user->is_active
            && ! User::where('role', 'admin')
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->exists()
        ) {
            return back()->with('error', 'Admin terakhir tidak boleh dihapus.');
        }

        // Pertahankan identitas pembuat dan verifikator transaksi.
        // Dicek lewat query langsung supaya tetap jalan sebelum model Simpanan dibuat.
        if (
            Schema::hasTable('simpanan')
            && DB::table('simpanan')
                ->where('dibuat_oleh', $user->id)
                ->orWhere('diverifikasi_oleh', $user->id)
                ->exists()
        ) {
            return back()->with(
                'error',
                'User memiliki riwayat transaksi. Nonaktifkan akun, jangan hapus.'
            );
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}