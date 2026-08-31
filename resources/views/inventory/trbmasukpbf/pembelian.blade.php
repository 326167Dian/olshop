@extends('inventory.layouts.app')

@section('header', 'Filter Pembelian')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Cek Total Pembelian Berdasarkan Tanggal</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.trbmasukpbf.pembelian.result') }}" target="_blank" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Tampil</button>
                </div>
            </form>
            <a href="{{ route('inventory.trbmasukpbf.index') }}" class="btn btn-sm btn-secondary mt-3">Kembali</a>
        </div>
    </div>
@endsection
