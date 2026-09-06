@extends('inventory.layouts.app')

@section('header', 'Stok Kritis')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">STOK KRITIS</h3>
        </div>
        <div class="card-body text-center">
            Analisa estimasi pengadaan barang berdasarkan frekuensi transaksi 30 hari terakhir
            <br><br>
            <form method="POST" action="{{ route('inventory.stok-kritis.recompute') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">PROSES ANALISA DATA</button>
            </form>
            <br><br>
            <a class="btn btn-success" href="{{ route('inventory.stok-kritis.estimasi') }}">TAMPILKAN ESTIMASI STOK KRITIS</a>
            <br><br>
            <a class="btn btn-warning" href="{{ route('inventory.stok-kritis.overstok') }}">TAMPILKAN OVERSTOK</a>
        </div>
    </div>
@endsection
