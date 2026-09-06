@extends('inventory.layouts.app')

@section('header', 'Laporan Komisi Pegawai')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Komisi {{ $petugas }} dari tanggal {{ $tglAwal }} s/d {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-detail" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30" class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Nilai Komisi</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td width="30">{{ $i + 1 }}</td>
                            <td>{{ $r->nmbrg_dtrkasir }}</td>
                            <td class="text-center">{{ $r->qty_dtrkasir }}</td>
                            <td class="text-right">Rp. {{ number_format($r->qty_dtrkasir > 0 ? $r->komisi / $r->qty_dtrkasir : 0, 0, ',', '.') }}</td>
                            <td class="text-right">Rp. {{ number_format((float) $r->komisi, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="font-weight:bold; background-color:aqua; font-size:large;">
                    <tr>
                        <td colspan="4" class="text-right">Total Komisi Produk</td>
                        <td class="text-right">Rp. {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-detail').DataTable({ order: [] });
    });
</script>
@endpush
