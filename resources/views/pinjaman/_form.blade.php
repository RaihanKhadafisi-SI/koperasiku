<div class="form-group">
    <label>Nama Anggota <span class="text-danger">*</span></label>
    <select name="anggota_id"
            class="form-control @error('anggota_id') is-invalid @enderror" required>
        <option value="">-- Pilih Anggota --</option>
        @foreach($anggotas as $anggota)
            <option value="{{ $anggota->id }}"
                @selected((string) old('anggota_id', $pinjaman->anggota_id ?? '') === (string) $anggota->id)>
                {{ $anggota->no_anggota }} — {{ $anggota->nama }}
            </option>
        @endforeach
    </select>
    @error('anggota_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Jumlah Pengajuan (Rp)</label>
    <input type="number" name="jumlah_pengajuan" min="1" step="0.01"
           class="form-control @error('jumlah_pengajuan') is-invalid @enderror"
           value="{{ old('jumlah_pengajuan', $pinjaman->jumlah_pengajuan ?? '') }}" required>
    @error('jumlah_pengajuan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Bunga per Tahun (%)</label>
            <input type="number" name="bunga_persen" min="0" max="100" step="0.01"
                   class="form-control @error('bunga_persen') is-invalid @enderror"
                   value="{{ old('bunga_persen', $pinjaman->bunga_persen ?? 0) }}" required>
            @error('bunga_persen')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Tenor (Bulan)</label>
            <input type="number" name="tenor_bulan" min="1" max="120"
                   class="form-control @error('tenor_bulan') is-invalid @enderror"
                   value="{{ old('tenor_bulan', $pinjaman->tenor_bulan ?? '') }}" required>
            @error('tenor_bulan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Tujuan Pinjaman</label>
    <textarea name="tujuan" rows="4"
              class="form-control @error('tujuan') is-invalid @enderror"
              required>{{ old('tujuan', $pinjaman->tujuan ?? '') }}</textarea>
    @error('tujuan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary">
    <i class="fas fa-save"></i> Simpan
</button>
<a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">Kembali</a>