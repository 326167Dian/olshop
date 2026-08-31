@extends('inventory.layouts.app')

@section('header', 'Detail Pembelian Distributor')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Rekap Detail Pembelian dari {{ $supplier->nm_supplier }} ({{ $tglAwal }} s/d {{ $tglAkhir }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Jatuh Tempo</th>
                            <th>Nilai Transaksi</th>
                            <th>Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->kd_trbmasuk }}</td>
                                <td>{{ $row->ket_trbmasuk }}</td>
                                <td class="text-center">{{ $row->tgl_trbmasuk?->format('Y-m-d') }}</td>
                                <td class="text-center">{{ $row->jatuhtempo }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($row->ttl_trbmasuk, 0, ',', '.') }}</td>
                                <td class="text-center text-primary">{{ $row->carabayar }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th colspan="2" class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
