@extends('inventory.layouts.app')

@section('header', 'Nilai Stok & Traffic Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Penjualan {{ $barang->nm_barang }} (stok = {{ $barang->stok_barang }}) dengan Total Keluar {{ $totalKeluar }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tblRiwayatJual" class="table table-condensed table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">No Transaksi</th>
                        <th class="text-center">No Pesanan</th>
                        <th class="text-center">Konsumen</th>
                        <th class="text-center">Kasir</th>
                        <th class="text-center">Harga Jual</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">No Batch</th>
                        <th class="text-center">Exp Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan as $r)
                        <tr>
                            <td class="text-center">{{ $r->kd_trkasir }}</td>
                            <td class="text-center">{{ $r->kodetx }}</td>
                            <td class="text-center">{{ $r->nm_pelanggan }}</td>
                            <td class="text-center">{{ $r->petugas }}</td>
                            <td class="text-center">{{ $r->hrgjual_dtrkasir }}</td>
                            <td class="text-center">{{ $r->tgl_trkasir }}</td>
                            <td class="text-center">{{ $r->qty_dtrkasir }}</td>
                            <td class="text-center">{{ $r->no_batch }}</td>
                            <td class="text-center">{{ $r->exp_date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pembelian {{ $barang->nm_barang }} dengan total masuk = {{ $totalMasuk }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tblRiwayatBeli" class="table table-condensed table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">id Transaksi</th>
                        <th class="text-center">No Transaksi</th>
                        <th class="text-center">No Faktur</th>
                        <th class="text-center">Suplier</th>
                        <th class="text-center">Petugas Input</th>
                        <th class="text-center">HNA</th>
                        <th class="text-center">Diskon(%)</th>
                        <th class="text-center">HrgNet</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">No Batch</th>
                        <th class="text-center">Exp Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pembelian as $q)
                        <tr>
                            <td class="text-center">{{ $q->id_dtrbmasuk }}</td>
                            <td class="text-center">{{ $q->kd_trbmasuk }}</td>
                            <td class="text-center">{{ $q->ket_trbmasuk }}</td>
                            <td class="text-center">{{ $q->nm_supplier }}</td>
                            <td class="text-center">{{ $q->petugas }}</td>
                            <td class="text-center">{{ number_format((float) $q->hnasat_dtrbmasuk, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $q->diskon }}</td>
                            <td class="text-center">{{ number_format((float) $q->hrgsat_dtrbmasuk, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $q->tgl_trbmasuk }}</td>
                            <td class="text-center">{{ $q->qty_dtrbmasuk }}</td>
                            <td class="text-center">{{ $q->no_batch }}</td>
                            <td class="text-center">{{ $q->exp_date }}</td>
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
        $('#tblRiwayatJual').DataTable({ order: [] });
        $('#tblRiwayatBeli').DataTable({ order: [] });
    });
</script>
@endpush
