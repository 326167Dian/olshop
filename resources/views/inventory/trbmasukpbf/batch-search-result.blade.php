@extends('inventory.layouts.app')

@section('header', 'Cari No. Batch')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Hasil Pencarian No. Batch: {{ $noBatch }}</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('inventory.trbmasukpbf.batch-search.form') }}" class="btn btn-sm btn-secondary mb-3">Cari Lagi</a>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Kadaluarsa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->kd_trbmasuk }}</td>
                                <td class="text-center">{{ \Illuminate\Support\Carbon::parse($row->tgl_trbmasuk)->format('Y-m-d') }}</td>
                                <td>{{ $row->nm_supplier }}</td>
                                <td>{{ $row->kd_barang }}</td>
                                <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                                <td class="text-end">{{ $row->qty_dtrbmasuk }}</td>
                                <td class="text-center">{{ $row->sat_dtrbmasuk }}</td>
                                <td class="text-center">{{ \Illuminate\Support\Carbon::parse($row->exp_date)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
