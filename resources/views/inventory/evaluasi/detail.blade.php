@extends('inventory.layouts.app')

@section('header', 'Evaluasi Pegawai')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Produksi {{ $petugas }} dari tanggal {{ $tglAwal }} s/d {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-detail-evaluasi" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30" class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Laba</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td width="30">{{ $i + 1 }}</td>
                            <td>{{ $r->nmbrg_dtrkasir }}</td>
                            <td class="text-center">{{ $r->qty_dtrkasir }}</td>
                            <td class="text-right">Rp. {{ number_format($r->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="font-weight:bold; background-color:aqua; font-size:large;">
                    <tr>
                        <td colspan="3" class="text-right">Total Produksi Laba</td>
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
        $('#tabel-detail-evaluasi').DataTable({ order: [] });
    });
</script>
@endpush
