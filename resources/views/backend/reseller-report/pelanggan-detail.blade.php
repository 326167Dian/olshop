@extends('backend.layouts.app')

@section('title', 'Rincian Transaksi Pelanggan')
@section('header', 'Rincian Transaksi Pelanggan')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('reseller-report.show', $reseller->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <p class="text-muted mb-1">Reseller</p>
                <h5 class="card-title mb-3">{{ $reseller->nama_lengkap }}</h5>
                <p class="text-muted mb-1">Pelanggan</p>
                <h6 class="mb-0">{{ $pelanggan->name }}</h6>
                <p class="text-muted mb-0">{{ $pelanggan->email }} -- {{ $pelanggan->no_tlp ?? '-' }}</p>
            </div>
        </div>

        @php
            $totalBelanja = $orders->sum('total_harga');
            $totalKomisi = round($totalBelanja * $komisiPersen / 100, 2);
        @endphp

        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Total Belanja (Selesai)</p>
                        <h4 class="fw-bolder mb-0">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Komisi Reseller ({{ rtrim(rtrim(number_format($komisiPersen, 2, ',', '.'), '0'), ',') }}%)</p>
                        <h4 class="fw-bolder mb-0 text-success">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rincian Transaksi</h3>
            </div>
            <div class="card-body">
                @if ($orders->isEmpty())
                    <p class="text-muted mb-0">Belum ada transaksi yang selesai dari pelanggan ini.</p>
                @else
                    @foreach ($orders as $order)
                    <div class="mb-4">
                        <h6 class="fw-bold">
                            {{ $order->kode_pesanan ?? ('Pesanan #' . $order->id) }}
                            <span class="text-muted fw-normal">&mdash; {{ $order->updated_at->format('d/m/Y H:i') }}</span>
                        </h6>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderItems as $item)
                                @php
                                    $subtotal = $item->harga * $item->quantity;
                                    $komisiItem = round($subtotal * $komisiPersen / 100, 2);
                                @endphp
                                <tr>
                                    <td>{{ $item->produk->nm_barang ?? '-' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($komisiItem, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    @media print {
        .no-print, .side-nav, #header, .nav-toggle-btn {
            display: none !important;
        }
    }
</style>
@endsection
