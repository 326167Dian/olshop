@extends('inventory.layouts.app')

@section('header', 'Laporan Stok Opname')

@section('content')
    @php($admin = Auth::guard('admin')->user())

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LAPORAN STOK OPNAME TANGGAL {{ $tglAwal }} SD {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body">
            @if ($admin->isPemilik())
                <form method="POST" action="{{ route('inventory.lapstokopname.sinkron-minus') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="tgl_awal" value="{{ $tglAwal }}">
                    <input type="hidden" name="tgl_akhir" value="{{ $tglAkhir }}">
                    <input type="hidden" name="shift" value="{{ $shift }}">
                    <button type="submit" class="btn btn-success btn-flat" onclick="return confirm('Sinkronisasi akan mencatat SEMUA item minus pada rentang ini sebagai transaksi penjualan dan MENGURANGI stok riil secara permanen. Lanjutkan?');">SINKRONISASI STOK MINUS</button>
                </form>
                <form method="POST" action="{{ route('inventory.lapstokopname.sinkron-plus') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="tgl_awal" value="{{ $tglAwal }}">
                    <input type="hidden" name="tgl_akhir" value="{{ $tglAkhir }}">
                    <input type="hidden" name="shift" value="{{ $shift }}">
                    <button type="submit" class="btn btn-warning btn-flat" onclick="return confirm('Sinkronisasi akan mencatat SEMUA item lebih pada rentang ini sebagai transaksi pembelian dan MENAMBAH stok riil secara permanen. Lanjutkan?');">SINKRONISASI STOK PLUS</button>
                </form>
            @endif

            <div class="table-responsive mt-3">
                <table id="tabel-detail-so" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas</th>
                            <th style="text-align:left">Nama Barang</th>
                            <th class="text-right">Satuan</th>
                            <th class="text-center">Exp</th>
                            <th class="text-right">JmlED</th>
                            <th class="text-right">SS</th>
                            <th class="text-center">SF</th>
                            <th class="text-center">Hasil</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detail as $i => $r)
                            <tr @if ($r->ttl_hrgbrg < 0) style="background-color:#FF00FF" @endif>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->nama_lengkap }}</td>
                                <td>{{ $r->nm_barang }}</td>
                                <td>{{ $r->sat_barang }}</td>
                                <td>{{ $r->exp_date }}</td>
                                <td class="text-center">{{ $r->jml }}</td>
                                <td class="text-center">{{ $r->stok_sistem }}</td>
                                <td class="text-center">{{ $r->stok_fisik }}</td>
                                <td class="text-center">{{ $r->selisih }}</td>
                                <td class="text-right">{{ number_format((float) $r->hrgsat_barang, 0, ',', '.') }}</td>
                                <td class="text-right" style="font-weight:bold">{{ number_format((float) $r->ttl_hrgbrg, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Belum ada data stok opname untuk tanggal &amp; shift ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot style="background-color:aqua; font-weight:bold; font-size:large;">
                        <tr>
                            <td colspan="9" class="text-right">Total Barang Minus</td>
                            <td colspan="2" class="text-right">Rp. {{ number_format((float) $totals->minus, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="9" class="text-right">Total Barang Lebih</td>
                            <td colspan="2" class="text-right">Rp. {{ number_format((float) $totals->plus, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REKAP PER JENIS OBAT (RAK) - SUDAH/BELUM DICEK</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-rekap-jenis" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th style="text-align:left">Jenis Obat (Rak)</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Sudah Dicek</th>
                        <th class="text-center">Belum Dicek</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php($totalItemSemua = 0)
                    @php($totalSudahSemua = 0)
                    @php($totalBelumSemua = 0)
                    @foreach ($rekapJenis as $i => $rj)
                        @php($totalItemSemua += $rj->total_item)
                        @php($totalSudahSemua += $rj->sudah_dicek)
                        @php($totalBelumSemua += $rj->belum_dicek)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $rj->jenisobat === '' ? '(Tanpa Jenis Obat)' : $rj->jenisobat }}</td>
                            <td class="text-center">{{ $rj->total_item }}</td>
                            <td class="text-center">{{ $rj->sudah_dicek }}</td>
                            <td class="text-center" @if ($rj->belum_dicek > 0) style="color:#dd4b39;font-weight:bold;" @endif>{{ $rj->belum_dicek }}</td>
                            <td class="text-center">
                                <a class="btn btn-xs btn-primary" target="_blank" href="{{ route('inventory.lapstokopname.detail-belum-dicek', ['jenisobat' => $rj->jenisobat, 'tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir, 'shift' => $shift]) }}">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background-color:aqua; font-weight:bold;">
                    <tr>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td class="text-center">{{ $totalItemSemua }}</td>
                        <td class="text-center">{{ $totalSudahSemua }}</td>
                        <td class="text-center">{{ $totalBelumSemua }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-detail-so').DataTable({
            order: [],
            aLengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            iDisplayLength: 25,
        });
        $('#tabel-rekap-jenis').DataTable({
            order: [],
            aLengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            iDisplayLength: 25,
        });
    });
</script>
@endpush
