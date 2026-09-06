@extends('inventory.layouts.app')

@section('header', 'Evaluasi Pegawai')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EVALUASI KINERJA PEGAWAI DARI TANGGAL {{ $tglAwal }} S/D {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-evaluasi" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Petugas</th>
                        <th>Laba Penjualan</th>
                        <th>Omzet Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td width="10">{{ $i + 1 }}</td>
                            <td>{{ $r->nama_lengkap }}</td>
                            <td class="text-right" style="font-weight:bold">
                                <a href="{{ route('inventory.evaluasi.detail', ['id' => $r->id_admin, 'tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir]) }}" target="_blank">
                                    Rp. {{ number_format($r->laba, 0, ',', '.') }}
                                </a>
                            </td>
                            <td class="text-right">Rp. {{ number_format($r->omzet, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="font-weight:bold; background-color:aqua; font-size:large;">
                    <tr>
                        <td colspan="2" class="text-center">Grand Total</td>
                        <td class="text-right">Rp. {{ number_format($totalLaba, 0, ',', '.') }}</td>
                        <td class="text-right">Rp. {{ number_format($totalOmzet, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-evaluasi').DataTable({ order: [] });
    });
</script>
@endpush
