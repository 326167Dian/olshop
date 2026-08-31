@extends('inventory.layouts.app')

@section('header', 'Tagihan Jatuh Tempo')

@section('content')
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Tagihan Jatuh Tempo ({{ $tglAwal }} s/d {{ $tglAkhir }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tgl Jatuh Tempo</th>
                            <th>Distributor</th>
                            <th>Nilai Tagihan</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->jatuhtempo }}</td>
                                <td>{{ $row->nm_supplier }}</td>
                                <td class="text-end">Rp {{ number_format($row->hutang, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('inventory.trbmasukpbf.jatuh-tempo.detail', ['tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir, 'id' => $row->id_supplier]) }}"
                                        target="_blank" class="btn btn-warning btn-xs">Tampil</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada tagihan jatuh tempo pada rentang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th colspan="2" class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
