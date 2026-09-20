<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>Detail Pelanggan | {{ $companySetting->nama_perusahaan }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <a href="{{ route('reseller.home') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ $pelanggan->name }}</h5>
                <p class="text-muted mb-0">{{ $pelanggan->email }}</p>
                <p class="text-muted mb-0">{{ $pelanggan->no_tlp ?? '-' }}</p>
            </div>
        </div>

        @php
            $totalBelanja = $orders->sum('total_harga');
            $totalKomisi = round($totalBelanja * $komisiPersen / 100, 2);
        @endphp

        <div class="row mb-4">
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
                        <p class="text-muted mb-1">Total Komisi Anda ({{ rtrim(rtrim(number_format($komisiPersen, 2, ',', '.'), '0'), ',') }}%)</p>
                        <h4 class="fw-bolder mb-0 text-success">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Barang yang Dibeli</h5>
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
                        <div class="table-responsive">
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
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Core Vendors JS -->
    <script src="{{ asset('newadmin/assets/js/vendors.min.js') }}"></script>
    <!-- Core JS -->
    <script src="{{ asset('newadmin/assets/js/app.min.js') }}"></script>
</body>

</html>
