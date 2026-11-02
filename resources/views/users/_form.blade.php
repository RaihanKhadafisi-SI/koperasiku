<div class="row">
    <div class="col-md-6 form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Email Login</label>
        <input type="email" name="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Role</label>
        <select name="role"
                class="form-control @error('role') is-invalid @enderror" required>
            @foreach([
                'admin' => 'Administrator',
                'pengurus' => 'Pengurus',
                'bendahara' => 'Bendahara',
                'anggota' => 'Anggota',
            ] as $key => $label)
                <option value="{{ $key }}"
                    @selected(old('role', $user->role ?? 'anggota') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Status Akun</label>
        <select name="is_active" class="form-control" required>
            <option value="1"
                @selected((string) old('is_active', isset($user) ? (int) $user->is_active : 1) === '1')>
                Aktif
            </option>
            <option value="0"
                @selected((string) old('is_active', isset($user) ? (int) $user->is_active : 1) === '0')>
                Nonaktif
            </option>
        </select>
    </div>

    <div class="col-md-6 form-group">
        <label>
            Password
            @if(isset($user))
                <span class="text-muted">(kosongkan jika tidak diubah)</span>
            @endif
        </label>
        <input type="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               minlength="8" autocomplete="new-password"
               {{ isset($user) ? '' : 'required' }}>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation"
               class="form-control" minlength="8"
               autocomplete="new-password"
               {{ isset($user) ? '' : 'required' }}>
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary mr-2">
        <i class="fas fa-save"></i> Simpan User
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
</div>