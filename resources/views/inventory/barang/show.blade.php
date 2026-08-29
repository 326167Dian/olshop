@extends('inventory.layouts.app')

@section('header', 'Detail Barang')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Detail Barang</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr style="background-color:#e6ffff;">
                    <th style="width:220px;">Nama Barang</th>
                    <td>{{ $barang->nm_barang }}</td>
                </tr>
                <tr>
                    <th>Satuan Retail</th>
                    <td>{{ $barang->sat_barang }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Satuan Grosir</th>
                    <td>{{ $barang->sat_grosir }}</td>
                </tr>
                <tr>
                    <th>Stok Retail</th>
                    <td>{{ rtrim(rtrim(number_format($barang->stok_barang, 2, ',', '.'), '0'), ',') }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Stok Grosir</th>
                    <td>{{ $barang->konversi > 0 ? round($barang->stok_barang / $barang->konversi) : 0 }}</td>
                </tr>
                <tr>
                    <th>Jenis Obat / Rak Obat</th>
                    <td>{{ $barang->jenisobat }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Konversi</th>
                    <td>{{ $barang->konversi }}</td>
                </tr>
                <tr style="background-color:#ffe6f5;">
                    <th>Harga Nett Apotek</th>
                    <td>Rp {{ number_format($barang->hna, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Harga Beli Retail</th>
                    <td>Rp {{ number_format($barang->hrgsat_barang, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Harga Beli Grosir</th>
                    <td>Rp {{ number_format($barang->hrgsat_grosir, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Harga Jual Reguler</th>
                    <td>Rp {{ number_format($barang->hrgjual_barang, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Harga Jual Resep</th>
                    <td>Rp {{ number_format($barang->hrgjual_barang1, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color:#e6ffff;">
                    <th>Harga Jual Marketplace</th>
                    <td>Rp {{ number_format($barang->hrgjual_barang2, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Komposisi dan Indikasi</th>
                    <td>{!! $barang->indikasi !!}</td>
                </tr>
            </table>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('inventory.barang.edit', $barang->id_barang) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('inventory.barang.index') }}" class="btn btn-success">Kembali</a>
        </div>
    </div>
@endsection
