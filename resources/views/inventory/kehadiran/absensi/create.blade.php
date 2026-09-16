@extends('inventory.layouts.app')

@section('header', 'Input Absensi Manual')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Absensi Manual</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.absensi.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="id_admin">Pegawai</label>
                    <select class="form-control @error('id_admin') is-invalid @enderror" name="id_admin" required>
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach ($pegawaiList as $p)
                            <option value="{{ $p->id_admin }}" {{ old('id_admin') == $p->id_admin ? 'selected' : '' }}>{{ $p->nama_lengkap }} ({{ $p->username }})</option>
                        @endforeach
                    </select>
                    @error('id_admin') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="id_shift">Shift (opsional)</label>
                        <select class="form-control @error('id_shift') is-invalid @enderror" name="id_shift">
                            <option value="">-- Tanpa Shift --</option>
                            @foreach ($shiftList as $s)
                                <option value="{{ $s->id_shift }}" {{ old('id_shift') == $s->id_shift ? 'selected' : '' }}>{{ $s->nama_shift }}</option>
                            @endforeach
                        </select>
                        @error('id_shift') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->toDateString()) }}" required>
                        @error('tanggal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="jam_masuk">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="jam_masuk" class="form-control" value="{{ old('jam_masuk') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="jam_pulang">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="jam_pulang" class="form-control" value="{{ old('jam_pulang') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="status">Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                            <option value="hadir" {{ old('status', 'hadir') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="terlambat" {{ old('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                            <option value="alpha" {{ old('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            <option value="izin" {{ old('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="cuti" {{ old('status') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                        @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.absensi.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
