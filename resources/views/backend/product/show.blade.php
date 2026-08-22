@extends('backend.layouts.app')

@section('title', 'Detail Produk')
@section('header', 'Detail Produk')

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- Card Detail Produk -->
        <div class="card card-primary shadow">
            <div class="card-header">
                <h3 class="card-title">Informasi Produk</h3>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <!-- Kolom Kiri (Gambar Produk) -->
                    <div class="col-md-3 text-center">
                        <label>Gambar Produk</label>
                        <div class="border p-2 rounded bg-light">
                            <img src="{{ asset('storage/' . ($product->image ?? 'default.png')) }}"
                                class="img-fluid img-thumbnail" style="height: 423px; width: auto; object-fit: contain;"
                                alt="Gambar Produk">
                        </div>
                    </div>

                    <!-- Kolom Kanan (Detail Produk) -->
                    <div class="col-md-8">
                        <div class="text-center">
                            <label>Detail Produk</label>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th width="30%">Nama Produk</th>
                                <td>{{ $product->nm_barang }}</td>
                            </tr>
                            <tr>
                                <th>Kode</th>
                                <td>{{ $product->kd_barang }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Obat</th>
                                <td>{{ $product->jenisobat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Satuan produk</th>
                                <td>{{ $product->sat_barang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Harga Netto Apotek</th>
                                <td>Rp {{ number_format($product->hna, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                            <tr>
                                <th>Indikasi</th>
                                <td>{{ $product->indikasi }}</td>
                            </tr>
                            <th>Harga Produk</th>
                            <td>Rp {{ number_format($product->hrgjual_barang2, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Stok</th>
                                <td>{{ $product->stok_barang }}</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>{{ $product->ket_barang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $product->status ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('product.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>
@endsection