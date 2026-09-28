@extends('inventory.layouts.app')

@section('header', 'Rekap Gaji Pegawai')

@push('css')
    <style>
        @media print {

            .no-print,
            .side-nav,
            .header-nav {
                display: none !important;
            }

            .main .content {
                margin: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekap Gaji Pegawai</h3>
        </div>
        <div class="card-body">
            <div class="no-print mb-3">
                <a href="{{ route('inventory.rekapgaji.index') }}" class="btn btn-secondary btn-sm">Ganti Periode</a>
                <button class="btn btn-info btn-sm" onclick="window.print()">Cetak</button>
            </div>

            <h4 class="text-center"><b>REKAPAN PENGELUARAN GAJI KARYAWAN</b></h4>
            <h4 class="text-center">{{ \App\Models\GajiDetail::namaBulan($bulan) }} {{ $tahun }}</h4>

            <table class="table table-bordered table-striped mt-3">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Gaji</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                            <td>{{ ($row->admin->blokir ?? 'N') == 'Y' ? 'Tidak Aktif' : 'Aktif' }}</td>
                            <td class="text-end">{{ number_format($row->total, 0, ',', '.') }}</td>
                            <td class="no-print">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('inventory.gajidetail.edit', $row->id_gaji_detail) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('inventory.gajidetail.cetak', $row->id_gaji_detail) }}"
                                        target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-print"></i> Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
