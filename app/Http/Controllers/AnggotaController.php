<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('search');

        $anggota = Anggota::query()
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('no_anggota', 'like', "%{$keyword}%")
                      ->orWhere('nik', 'like', "%{$keyword}%")
                      ->orWhere('nama', 'like', "%{$keyword}%")
                      ->orWhere('no_hp', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('anggota.index', compact('anggota', 'keyword'));
    }

    public function create()
    {
        return view('anggota.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_anggota' => ['required', 'string', 'max:30', 'unique:anggota,no_anggota'],
            'nik' => ['required', 'digits:16', 'unique:anggota,nik'],
            'nama' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:anggota,email'],
            'tanggal_bergabung' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        Anggota::create($validated);

        return redirect()
            ->route('anggota.index')
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function show(Anggota $anggota)
    {
        return view('anggota.show', compact('anggota'));
    }

    public function edit(Anggota $anggota)
    {
        return view('anggota.edit', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $validated = $request->validate([
            'no_anggota' => [
                'required', 'string', 'max:30',
                Rule::unique('anggota', 'no_anggota')->ignore($anggota->id),
            ],
            'nik' => [
                'required', 'digits:16',
                Rule::unique('anggota', 'nik')->ignore($anggota->id),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('anggota', 'email')->ignore($anggota->id),
            ],
            'tanggal_bergabung' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $anggota->update($validated);

        return redirect()
            ->route('anggota.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota)
    {
        // Anggota yang memiliki transaksi keuangan tidak boleh dihapus.
        // Dicek lewat query langsung supaya tetap jalan sebelum model
        // Simpanan dan Pinjaman dibuat.
        $punyaSimpanan = Schema::hasTable('simpanan')
            && DB::table('simpanan')->where('anggota_id', $anggota->id)->exists();

        $punyaPinjaman = Schema::hasTable('pinjamans')
            && DB::table('pinjamans')->where('anggota_id', $anggota->id)->exists();

        if ($punyaSimpanan || $punyaPinjaman) {
            return redirect()
                ->route('anggota.index')
                ->with('error', 'Anggota memiliki riwayat transaksi. Ubah status menjadi nonaktif.');
        }

        $anggota->delete();

        return redirect()
            ->route('anggota.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}