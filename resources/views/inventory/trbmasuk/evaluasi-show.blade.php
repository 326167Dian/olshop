@extends('inventory.layouts.app')

@section('header', 'Evaluasi Barang Masuk')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Evaluasi — {{ $trbmasuk->kd_trbmasuk }} (dari pesanan {{ $trbmasuk->kd_orders }})</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.trbmasuk.evaluasi.index') }}">Kembali</a>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th>Qty Pesan</th>
                            <th>Hrg Pesan</th>
                            <th>Qty Masuk</th>
                            <th>Hrg Masuk</th>
                            <th>Selisih Qty</th>
                            <th>Total Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            @php $selisih = $row->qty_masuk - $row->qty_pesan; @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->kd_barang }}</td>
                                <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                                <td class="text-center">{{ $row->sat_dtrbmasuk }}</td>
                                <td class="text-end">{{ $row->qty_pesan }}</td>
                                <td class="text-end">{{ number_format($row->hrgsat_pesan, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $row->qty_masuk }}</td>
                                <td class="text-end">{{ number_format($row->hrgsat_masuk, 0, ',', '.') }}</td>
                                <td class="text-end {{ $selisih < 0 ? 'text-danger fw-bold' : '' }}">{{ $selisih }}</td>
                                <td class="text-end">{{ number_format($row->totalharga_masuk, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
