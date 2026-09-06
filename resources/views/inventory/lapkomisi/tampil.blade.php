@extends('inventory.layouts.app')

@section('header', 'Laporan Komisi Pegawai')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LAPORAN KOMISI PEGAWAI DARI TANGGAL {{ $tglAwal }} S/D {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-lapkomisi" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Petugas</th>
                        <th>Komisi Produk</th>
                        <th>Komisi Global</th>
                        <th>SubTotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td width="10">{{ $i + 1 }}</td>
                            <td>{{ $r->nama_lengkap }}</td>
                            <td class="text-right" style="font-weight:bold">
                                <a href="{{ route('inventory.lapkomisi.detail', ['id' => $r->id_admin, 'tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir]) }}" target="_blank">
                                    Rp. {{ number_format($r->komisi_produk, 0, ',', '.') }}
                                </a>
                            </td>
                            <td class="text-right">Rp. {{ number_format($r->komisi_global, 0, ',', '.') }}</td>
                            <td class="text-right">Rp. {{ number_format($r->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="font-weight:bold; background-color:aqua; font-size:large;">
                    <tr>
                        <td colspan="2" class="text-center">Grand Total Komisi</td>
                        <td class="text-right">Rp. {{ number_format($totalPk, 0, ',', '.') }}</td>
                        <td class="text-right">Rp. {{ number_format($totalGlobal, 0, ',', '.') }}</td>
                        <td class="text-right">Rp. {{ number_format($totalSub, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-lapkomisi').DataTable({ order: [] });
    });
</script>
@endpush
