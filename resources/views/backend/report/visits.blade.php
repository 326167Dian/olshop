@extends('backend.layouts.app')
@section('title', 'Laporan Kunjungan Website')

@section('header', 'Halaman Laporan Kunjungan Website')

@section('content')
<div class="row mt-3 p-3">
    <div class="col-md-4 mb-3">
        <div class="card card-primary">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Kunjungan Halaman Utama</h6>
                <h2 class="mb-0">{{ number_format($totalVisits, 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Kunjungan Halaman Utama -- 30 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekap as $baris)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($baris['tanggal'])->translatedFormat('d M Y') }}</td>
                                <td>{{ number_format($baris['jumlah'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Kunjungan Per Halaman -- 30 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Halaman</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapHalaman as $baris)
                            <tr>
                                <td>{{ $baris['label'] }}</td>
                                <td>{{ number_format($baris['jumlah'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Belum ada data kunjungan per halaman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Kategori Produk Terbanyak Dikunjungi -- 30 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rekapKategori as $baris)
                            <tr>
                                <td>{{ $baris['nama'] }}</td>
                                <td>{{ number_format($baris['jumlah'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Belum ada data -- rincian per kategori baru mulai tercatat sejak fitur ini aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
