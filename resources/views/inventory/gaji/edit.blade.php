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
                    <label for="rate_lembur">Tarif Lembur 1 Shift</label>
                    <input type="number" step="0.01" min="0" name="rate_lembur" id="rate_lembur"
                        class="form-control @error('rate_lembur') is-invalid @enderror"
                        value="{{ old('rate_lembur', $gaji->rate_lembur) }}" required>
                    <small class="text-muted">Dipakai flat untuk setiap shift tambahan (ke-2+ di hari yang sama) yang tercatat otomatis dari Kehadiran Pegawai, tidak dikali jam kerja. Juga dipakai per jam untuk lembur yang diinput manual di menu Kehadiran Pegawai &gt; Lembur.</small>
                    @error('rate_lembur') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Potongan Keterlambatan</label>
                    <small class="text-muted d-block mb-2">Dihitung dari menit terlambat setelah toleransi_telat shift terlampaui (bukan dari jadwal mentah).</small>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="potongan_telat_15_30" class="small">Telat 15&ndash;30 Menit</label>
                            <input type="number" step="0.01" min="0" name="potongan_telat_15_30" id="potongan_telat_15_30"
                                class="form-control @error('potongan_telat_15_30') is-invalid @enderror"
                                value="{{ old('potongan_telat_15_30', $gaji->potongan_telat_15_30) }}" required>
                            @error('potongan_telat_15_30') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="potongan_telat_30_60" class="small">Telat 30&ndash;60 Menit</label>
                            <input type="number" step="0.01" min="0" name="potongan_telat_30_60" id="potongan_telat_30_60"
                                class="form-control @error('potongan_telat_30_60') is-invalid @enderror"
                                value="{{ old('potongan_telat_30_60', $gaji->potongan_telat_30_60) }}" required>
                            @error('potongan_telat_30_60') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="potongan_telat_60_lebih" class="small">Telat &gt;60 Menit</label>
                            <input type="number" step="0.01" min="0" name="potongan_telat_60_lebih" id="potongan_telat_60_lebih"
                                class="form-control @error('potongan_telat_60_lebih') is-invalid @enderror"
                                value="{{ old('potongan_telat_60_lebih', $gaji->potongan_telat_60_lebih) }}" required>
                            @error('potongan_telat_60_lebih') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
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
