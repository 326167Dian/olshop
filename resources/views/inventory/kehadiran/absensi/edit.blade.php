@extends('inventory.layouts.app')

@section('header', 'Koreksi Absensi')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Koreksi Absensi</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.absensi.update', $absensi->id_absensi) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Pegawai</label>
                        <input type="text" class="form-control" value="{{ $absensi->admin->nama_lengkap ?? '-' }}" disabled>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Tanggal</label>
                        <input type="text" class="form-control" value="{{ $absensi->tanggal->format('d-m-Y') }}" disabled>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Shift</label>
                        <input type="text" class="form-control" value="{{ $absensi->shift->nama_shift ?? '-' }}" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="jam_masuk">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="jam_masuk" class="form-control" value="{{ old('jam_masuk', $absensi->jam_masuk) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="jam_pulang">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang" class="form-control" value="{{ old('jam_pulang', $absensi->jam_pulang) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="status">Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                            @foreach (['hadir', 'terlambat', 'alpha', 'izin', 'cuti'] as $s)
                                <option value="{{ $s }}" {{ old('status', $absensi->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2">{{ old('keterangan', $absensi->keterangan) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.absensi.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
