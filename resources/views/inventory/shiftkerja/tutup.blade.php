@extends('inventory.layouts.app')

@section('header', 'Tutup Kasir')

@section('content')
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Tutup Kasir</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless mb-3">
                <tr><th style="width:160px;">Petugas Buka</th><td>: {{ $waktuKerja->petugasbuka }}</td></tr>
                <tr><th>Tanggal</th><td>: {{ $waktuKerja->tanggal }}</td></tr>
                <tr><th>Waktu Buka</th><td>: {{ $waktuKerja->waktubuka }}</td></tr>
                <tr><th>Saldo Awal</th><td>: {{ number_format($waktuKerja->saldoawal, 0, ',', '.') }}</td></tr>
            </table>

            <form method="POST" action="{{ route('inventory.shiftkerja.tutup.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Saldo Akhir</label>
                    <input type="number" step="any" name="saldoakhir" class="form-control" required autofocus>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventory.shiftkerja.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
