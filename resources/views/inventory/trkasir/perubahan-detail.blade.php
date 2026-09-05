@extends('inventory.layouts.app')

@section('header', 'Perubahan Transaksi')

@section('content')
    <a class="btn btn-secondary mb-3" href="{{ route('inventory.trkasir.perubahan') }}">&larr; Kembali ke Daftar</a>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Perubahan: {{ $kdTrkasir }}</h3>
        </div>
        <div class="card-body">
            <p>
                Status:
                @if ($statusTransaksi === 'AKTIF')
                    <span class="badge bg-success">AKTIF</span>
                @else
                    <span class="badge bg-danger">TRANSAKSI DIHAPUS</span>
                @endif

                @if ($header)
                    &nbsp; Pelanggan: <strong>{{ $header->nm_pelanggan }}</strong>
                    &nbsp; Tanggal: <strong>{{ $header->tgl_trkasir?->format('Y-m-d') }}</strong>
                    &nbsp; Jumlah Revisi: <strong>{{ (int) $header->tipetx }}</strong>
                @elseif ($headerRestore)
                    &nbsp; Pelanggan: <strong>{{ $headerRestore->nm_pelanggan }}</strong>
                    &nbsp; Tanggal: <strong>{{ $headerRestore->tgl_trkasir }}</strong>
                    &nbsp; Jumlah Revisi sebelum dihapus: <strong>{{ (int) $headerRestore->tipetx }}</strong>
                @endif
            </p>

            <div class="row">
                <div class="col-md-6">
                    <h5>Kondisi Awal (saat transaksi pertama)</h5>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($kondisiAwal as $rowAwal)
                                <tr>
                                    <td>{{ $rowAwal->nmbrg_dtrkasir }}</td>
                                    <td>{{ $rowAwal->qty_dtrkasir }}</td>
                                    <td>Rp {{ number_format($rowAwal->hrgttl_dtrkasir, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total Transaksi</th>
                                <th>Rp {{ number_format($totalAwal, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Kondisi Akhir (sekarang)</h5>
                    <table class="table table-bordered table-sm">
                        <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
                        <tbody>
                            @forelse ($kondisiAkhir as $rowAkhir)
                                <tr>
                                    <td>{{ $rowAkhir->nmbrg_dtrkasir }}</td>
                                    <td>{{ $rowAkhir->qty_dtrkasir }}</td>
                                    <td>Rp {{ number_format($rowAkhir->hrgttl_dtrkasir, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total Transaksi</th>
                                <th>Rp {{ number_format($totalAkhir, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <h5 class="mt-3">Perbandingan Item</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Item</th><th>Status</th><th>Qty Awal</th><th>Qty Akhir</th></tr></thead>
                <tbody>
                    @forelse ($diff as $d)
                        @php
                            $nama = $d['awal']->nmbrg_dtrkasir ?? $d['akhir']->nmbrg_dtrkasir ?? '';
                            $qtyAwal = $d['awal']->qty_dtrkasir ?? '-';
                            $qtyAkhir = $d['akhir']->qty_dtrkasir ?? '-';
                            $badgeClass = match ($d['status']) {
                                'DITAMBAHKAN' => 'bg-success',
                                'DIHAPUS' => 'bg-danger',
                                'DIUBAH' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ $nama }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $d['status'] }}</span></td>
                            <td>{{ $qtyAwal }}</td>
                            <td>{{ $qtyAkhir }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada perbedaan item</td></tr>
                    @endforelse
                </tbody>
            </table>

            <h5 class="mt-3">Riwayat Perubahan</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Waktu</th><th>Revisi Ke</th><th>Aksi</th><th>Keterangan</th><th>Admin</th></tr></thead>
                <tbody>
                    @forelse ($timeline as $t)
                        <tr>
                            <td>{{ $t['waktu'] }}</td>
                            <td>{{ (int) $t['tipetx'] }}</td>
                            <td>{{ $t['aksi'] }}</td>
                            <td>{{ $t['keterangan'] }}</td>
                            <td>{{ $adminNames[$t['id_admin']] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Tidak ada riwayat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
