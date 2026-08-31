@extends('inventory.layouts.app')

@section('header', 'Rekap Pembelian')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Rekap Pembelian berdasarkan Tanggal ({{ $tglAwal }} s/d {{ $tglAkhir }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pembelian</th>
                            <th>Kode Transaksi</th>
                            <th>Distributor</th>
                            <th>Status Pembayaran</th>
                            <th>Nilai Faktur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->tgl_trbmasuk?->format('Y-m-d') }}</td>
                                <td>{{ $row->kd_trbmasuk }}</td>
                                <td>{{ $row->nm_supplier }}</td>
                                <td>{{ $row->carabayar }}</td>
                                <td class="text-end">Rp {{ number_format($row->ttl_trbmasuk, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Lunas = Rp {{ number_format($totalLunas, 0, ',', '.') }}, Belum Lunas = Rp {{ number_format($totalKredit, 0, ',', '.') }}</th>
                            <th colspan="2" class="text-end">Total: Rp {{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
