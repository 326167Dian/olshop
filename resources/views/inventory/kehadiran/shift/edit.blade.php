@extends('inventory.layouts.app')

@section('header', 'Ubah Shift')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Shift</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.shift.update', $shift->id_shift) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nama_shift">Nama Shift</label>
                    <input type="text" name="nama_shift" id="nama_shift"
                        class="form-control @error('nama_shift') is-invalid @enderror" value="{{ old('nama_shift', $shift->nama_shift) }}" required>
                    @error('nama_shift') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="jam_masuk">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="jam_masuk"
                            class="form-control @error('jam_masuk') is-invalid @enderror" value="{{ old('jam_masuk', $shift->jam_masuk) }}" required>
                        @error('jam_masuk') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="jam_pulang">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang"
                            class="form-control @error('jam_pulang') is-invalid @enderror" value="{{ old('jam_pulang', $shift->jam_pulang) }}" required>
                        @error('jam_pulang') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="toleransi_telat">Toleransi Telat (menit)</label>
                        <input type="number" min="0" name="toleransi_telat" id="toleransi_telat"
                            class="form-control @error('toleransi_telat') is-invalid @enderror"
                            value="{{ old('toleransi_telat', $shift->toleransi_telat) }}" required>
                        @error('toleransi_telat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_1" value="1" {{ old('status_aktif', $shift->status_aktif ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_aktif_1">Aktif</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_aktif" id="status_aktif_0" value="0" {{ old('status_aktif', $shift->status_aktif ? '1' : '0') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status_aktif_0">Nonaktif</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.shift.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
