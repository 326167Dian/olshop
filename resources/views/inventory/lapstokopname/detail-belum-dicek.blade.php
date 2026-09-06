@extends('inventory.layouts.app')

@section('header', 'Laporan Stok Opname')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BARANG BELUM DICEK - RAK {{ $jenisobat === '' ? '(Tanpa Jenis Obat)' : $jenisobat }} ({{ $tglAwal }} SD {{ $tglAkhir }})</h3>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-danger btn-flat mb-3" onclick="window.close();">TUTUP</button>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th style="text-align:left">Kode Barang</th>
                            <th style="text-align:left">Nama Barang</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $r->kd_barang }}</td>
                                <td>{{ $r->nm_barang }}</td>
                                <td class="text-center">{{ $r->sat_barang }}</td>
                                <td class="text-center">{{ $r->stok_barang }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Semua item di rak ini sudah dicek.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
