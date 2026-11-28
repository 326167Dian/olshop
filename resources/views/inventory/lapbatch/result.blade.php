@extends('inventory.layouts.app')

@section('header', 'Laporan Dekat Expired')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Laporan Data Batch Expired Tanggal {{ $tglAwal }} hingga {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Batch</th>
                            <th class="text-center">Exp. Date</th>
                            <th class="text-center">Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $row->no_batch }}</td>
                                <td class="text-center">{{ $row->exp_date }}</td>
                                <td class="text-center">{{ $row->kd_barang }}</td>
                                <td>{{ $row->nm_barang }}</td>
                                <td class="text-center">{{ (int) $row->sisa_qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada batch yang kadaluarsa pada rentang tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
