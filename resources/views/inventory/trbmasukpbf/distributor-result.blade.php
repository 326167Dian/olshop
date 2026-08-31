@extends('inventory.layouts.app')

@section('header', 'Rekap Distributor')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Rekap Pembelian berdasarkan Distributor ({{ $tglAwal }} s/d {{ $tglAkhir }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Distributor</th>
                            <th>Lunas</th>
                            <th>Belum Lunas</th>
                            <th>Total</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->nm_supplier }}</td>
                                <td class="text-end">Rp {{ number_format($row->total_lunas, 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($row->total_kredit, 0, ',', '.') }}</td>
                                <td class="text-end text-primary">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('inventory.trbmasukpbf.distributor.detail', ['tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir, 'id' => $row->id_supplier]) }}"
                                        target="_blank" class="btn btn-success btn-xs">Show</a>
                                </td>
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
