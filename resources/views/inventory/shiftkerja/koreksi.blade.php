@extends('inventory.layouts.app')

@section('header', 'Tutup Kasir Koreksi')

@section('content')
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Tutup Kasir Koreksi</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.shiftkerja.koreksi.store', $waktuKerja->id_shift) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label class="form-label">Petugas Buka</label>
                    <input type="text" name="petugasbuka" class="form-control" value="{{ $waktuKerja->petugasbuka }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Petugas Tutup</label>
                    <select name="petugastutup" class="form-control">
                        <option value="">-</option>
                        @foreach ($petugasList as $nama)
                            <option value="{{ $nama }}" {{ $waktuKerja->petugastutup === $nama ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-control" required>
                        @foreach ($shiftList as $s)
                            <option value="{{ $s->shift }}" {{ (string) $waktuKerja->shift === (string) $s->shift ? 'selected' : '' }}>SHIFT {{ strtoupper($s->nama_shift) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $waktuKerja->tanggal }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Waktu Buka</label>
                    <input type="time" name="waktubuka" class="form-control" value="{{ $waktuKerja->waktubuka }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Waktu Tutup</label>
                    <input type="time" name="waktututup" class="form-control" value="{{ $waktuKerja->waktututup }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Saldo Awal</label>
                    <input type="number" step="any" name="saldoawal" class="form-control" value="{{ $waktuKerja->saldoawal }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Saldo Akhir</label>
                    <input type="number" step="any" name="saldoakhir" class="form-control" value="{{ $waktuKerja->saldoakhir }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="ON" {{ $waktuKerja->status === 'ON' ? 'selected' : '' }}>ON</option>
                        <option value="OFF" {{ $waktuKerja->status === 'OFF' ? 'selected' : '' }}>OFF</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventory.shiftkerja.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
