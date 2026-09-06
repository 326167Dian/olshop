@extends('inventory.layouts.app')

@section('header', 'Nilai Stok & Traffic Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pesanan {{ $barang->nm_barang }} (stok = {{ $barang->stok_barang }}) dengan Total Pesanan {{ $totalPesanan }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tblRiwayatPesanan" class="table table-condensed table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">No Pesanan</th>
                        <th class="text-center">Kasir</th>
                        <th class="text-center">Harga Beli</th>
                        <th class="text-center">Harga Jual</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayat as $r)
                        <tr>
                            <td class="text-center">
                                <a href="{{ route('inventory.orders.edit', ['order' => $r->id_trbmasuk]) }}">{{ $r->kd_trbmasuk }}</a>
                            </td>
                            <td>{{ $r->petugas }}</td>
                            <td class="text-center">{{ number_format((float) $r->hrgsat_dtrbmasuk, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format((float) $r->hrgjual_dtrbmasuk, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $r->tgl_trbmasuk }}</td>
                            <td class="text-center">{{ $r->qty_dtrbmasuk }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <button type="button" class="btn btn-success" onclick="history.back()">KEMBALI</button>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tblRiwayatPesanan').DataTable({ order: [] });
    });
</script>
@endpush
