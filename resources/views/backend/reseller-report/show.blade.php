@extends('backend.layouts.app')

@section('title', 'Detail Reseller')
@section('header', 'Halaman Detail Reseller')

@section('content')
<section class="content">
    <div class="container-fluid">

        <a href="{{ route('reseller-report.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ $reseller->nama_lengkap }}</h5>
                <p class="text-muted mb-0">No HP: {{ $reseller->no_hp }}</p>
                <p class="text-muted mb-0">Alamat: {{ $reseller->alamat }}</p>
                <p class="text-muted mb-0">Bank: {{ $reseller->nama_bank }} -- {{ $reseller->no_rekening }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Omzet Grup (Selesai)</p>
                        <h4 class="fw-bolder mb-0">Rp {{ number_format($omzet, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Estimasi Komisi ({{ rtrim(rtrim(number_format($komisiPersen, 2, ',', '.'), '0'), ',') }}%)</p>
                        <h4 class="fw-bolder mb-0 text-success">Rp {{ number_format($komisi, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Jumlah Pelanggan</p>
                        <h4 class="fw-bolder mb-0">{{ $pelanggan->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pelanggan yang Direkrut</h3>
            </div>
            <div class="card-body">
                @if ($pelanggan->isEmpty())
                    <p class="text-muted mb-0">Belum ada pelanggan yang mendaftar lewat link referral reseller ini.</p>
                @else
                    <table class="table table-auto table-sm table-bordered table-striped w-100" id="example1">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No HP</th>
                                <th>Total Pesanan</th>
                                <th>Total Belanja</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pelanggan as $index)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $index->name }}</td>
                                <td>{{ $index->email }}</td>
                                <td>{{ $index->no_tlp ?? '-' }}</td>
                                <td>{{ $index->total_pesanan }}</td>
                                <td>Rp {{ number_format($index->total_belanja ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('reseller-report.pelanggan', [$reseller->id, $index->id]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Rincian Transaksi
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#example1').DataTable({
            responsive: true,
            autoWidth: false
        });
    });
</script>
@endpush
