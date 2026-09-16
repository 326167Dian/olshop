@extends('inventory.layouts.app')

@section('header', 'Ubah Data Gaji Karyawan')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Data Gaji Karyawan</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.gaji.update', $gaji->id_gaji) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" class="form-control"
                        value="{{ ($gaji->admin->nama_lengkap ?? '-') . ' (' . ($gaji->admin->username ?? '-') . ')' }}"
                        disabled>
                    <small class="text-muted">Karyawan tidak bisa diubah di sini. Hapus lalu tambahkan ulang jika salah pilih.</small>
                </div>
                <div class="form-group">
                    <label for="gaji_harian">Gaji Pokok</label>
                    <input type="number" step="0.01" min="0" name="gaji_harian" id="gaji_harian"
                        class="form-control @error('gaji_harian') is-invalid @enderror"
                        value="{{ old('gaji_harian', $gaji->gaji_harian) }}" required>
                    @error('gaji_harian') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="transportasi_harian">Transportasi/Ekstra Fooding</label>
                    <input type="number" step="0.01" min="0" name="transportasi_harian" id="transportasi_harian"
                        class="form-control @error('transportasi_harian') is-invalid @enderror"
                        value="{{ old('transportasi_harian', $gaji->transportasi_harian) }}" required>
                    @error('transportasi_harian') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="rate_lembur">Tarif Lembur / Jam</label>
                    <input type="number" step="0.01" min="0" name="rate_lembur" id="rate_lembur"
                        class="form-control @error('rate_lembur') is-invalid @enderror"
                        value="{{ old('rate_lembur', $gaji->rate_lembur) }}" required>
                    <small class="text-muted">Dipakai untuk menghitung nominal lembur dari modul Kehadiran Pegawai saat ditarik ke Slip Gaji.</small>
                    @error('rate_lembur') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Status</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_1"
                            value="1" {{ old('status_aktif', $gaji->status_aktif ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_aktif_1">Aktif</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_0"
                            value="0" {{ old('status_aktif', $gaji->status_aktif ? '1' : '0') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_aktif_0">Nonaktif</label>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventory.gaji.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
