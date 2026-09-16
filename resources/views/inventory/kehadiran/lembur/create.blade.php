@extends('inventory.layouts.app')

@section('header', 'Input Lembur Manual')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Lembur Manual</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.lembur.store') }}">
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
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->toDateString()) }}" required>
                        @error('tanggal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="jam_lembur">Jam Lembur</label>
                        <input type="number" step="0.01" min="0.01" name="jam_lembur" id="jam_lembur"
                            class="form-control @error('jam_lembur') is-invalid @enderror" value="{{ old('jam_lembur') }}" required>
                        @error('jam_lembur') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
                </div>

                <p class="text-muted small mb-0">Lembur manual langsung berstatus Disetujui dan siap ditarik ke Slip Gaji periode berikutnya.</p>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.lembur.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
