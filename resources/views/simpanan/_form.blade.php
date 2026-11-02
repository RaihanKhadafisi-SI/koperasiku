<div class="row">
    <div class="col-md-6 form-group">
        <label>Anggota</label>
        <select name="anggota_id"
                class="form-control @error('anggota_id') is-invalid @enderror" required>
            <option value="">-- Pilih anggota --</option>
            @foreach($anggota as $item)
                <option value="{{ $item->id }}"
                    @selected((string) old('anggota_id', $simpanan->anggota_id ?? '') === (string) $item->id)>
                    {{ $item->no_anggota }} — {{ $item->nama }}
                </option>
            @endforeach
        </select>
        @error('anggota_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Jenis Simpanan</label>
        <select name="jenis"
                class="form-control @error('jenis') is-invalid @enderror" required>
            <option value="">-- Pilih jenis --</option>
            @foreach(['pokok' => 'Simpanan Pokok', 'wajib' => 'Simpanan Wajib', 'sukarela' => 'Simpanan Sukarela'] as $key => $label)
                <option value="{{ $key }}"
                    @selected(old('jenis', $simpanan->jenis ?? '') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('jenis')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Nominal (Rp)</label>
        <input type="number" name="nominal" min="1" step="0.01"
               class="form-control @error('nominal') is-invalid @enderror"
               value="{{ old('nominal', $simpanan->nominal ?? '') }}" required>
        @error('nominal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label>Tanggal Transaksi</label>
        <input type="date" name="tanggal_transaksi"
               max="{{ now()->toDateString() }}"
               class="form-control @error('tanggal_transaksi') is-invalid @enderror"
               value="{{ old('tanggal_transaksi', isset($simpanan) ? $simpanan->tanggal_transaksi->format('Y-m-d') : now()->toDateString()) }}"
               required>
        @error('tanggal_transaksi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 form-group">
        <label>Keterangan</label>
        <textarea name="keterangan" rows="3"
                  class="form-control @error('keterangan') is-invalid @enderror"
                  placeholder="Contoh: Simpanan wajib bulan November">{{ old('keterangan', $simpanan->keterangan ?? '') }}</textarea>
        @error('keterangan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary mr-2">
        <i class="fas fa-save"></i> Simpan Transaksi
    </button>
    <a href="{{ route('simpanan.index') }}" class="btn btn-secondary">Batal</a>
</div>