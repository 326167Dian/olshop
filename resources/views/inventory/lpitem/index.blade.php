@extends('inventory.layouts.app')

@section('header', 'Laporan Item Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Item Barang</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar seluruh barang beserta stok dan harga saat ini (tanpa filter, diurutkan
                nama barang).</p>

            <a href="{{ route('inventory.lpitem.cetak') }}" target="_blank" class="btn btn-primary">Cetak PDF</a>
            <a href="{{ route('inventory.lpitem.excel') }}" class="btn btn-success">Export Excel</a>
        </div>
    </div>
@endsection
