@extends('inventory.layouts.app')

@section('header', 'Detail Tagihan Jatuh Tempo')

@section('content')
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Detail Tagihan Jatuh Tempo</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.trbmasukpbf.mark-lunas') }}" target="_blank"
                onsubmit="return confirm('Apakah faktur yang dipilih sudah LUNAS?')">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th></th>
                                <th>No</th>
                                <th>Tgl Jatuh Tempo</th>
                                <th>Kode Transaksi</th>
                                <th>No Faktur</th>
                                <th>Distributor</th>
                                <th>Nilai Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $index => $row)
                                <tr>
                                    <td><input type="checkbox" name="kode[]" value="{{ $row->kd_trbmasuk }}"></td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->jatuhtempo }}</td>
                                    <td>{{ $row->kd_trbmasuk }}</td>
                                    <td>{{ $row->ket_trbmasuk }}</td>
                                    <td>{{ $row->nm_supplier }}</td>
                                    <td class="text-end">Rp {{ number_format($row->ttl_trbmasuk, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th colspan="3" class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($bisaPelunasan)
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Submit Pelunasan</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
