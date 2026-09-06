@extends('inventory.layouts.app')

@section('header', 'Bundle/Paket Produk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Paket Produk</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nama Paket Produk</label>
                        <input type="text" class="form-control" value="{{ $bundle->nm_bundle }}" disabled>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Satuan</label>
                        <input type="text" class="form-control" value="{{ $bundle->sat_bundle }}" disabled>
                    </div>
                </div>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="3%" class="text-center">No.</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Harga Jual</th>
                            <th class="text-center">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bundle->detail as $i => $d)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}.</td>
                                <td>{{ $d->nm_barang }}</td>
                                <td class="text-center">{{ $d->qty_barang }}</td>
                                <td class="text-center">{{ $d->sat_barang }}</td>
                                <td class="text-right">Rp. {{ number_format($d->hrgjual_barang, 0, ',', '.') }}</td>
                                <td class="text-right">Rp. {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Jumlah Kuota</label>
                        <input type="text" class="form-control" value="{{ $bundle->qty_bundle }}" disabled>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Harga Jual Bundling</label>
                        <input type="text" class="form-control" value="Rp. {{ number_format($bundle->hrgjual_bundle, 0, ',', '.') }}" disabled>
                    </div>
                </div>
            </div>

            <a class="btn btn-default" href="{{ route('inventory.bundle.index') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
@endsection
