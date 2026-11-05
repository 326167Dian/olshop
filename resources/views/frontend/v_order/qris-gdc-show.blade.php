@extends('frontend.layouts.index')
@section('content')
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default text-center" style="margin-top: 20px;">
                    <div class="panel-heading" style="background-color: #f5f5f5;">
                        <h4 class="panel-title" style="font-weight: bold; color: #d10024;">Bayar dengan QRIS</h4>
                    </div>
                    <div class="panel-body">
                        <p>Scan kode di bawah dengan aplikasi e-wallet atau m-banking apa pun yang mendukung QRIS.</p>

                        <div id="qris-status-badge" class="alert alert-warning">Menunggu pembayaran...</div>

                        @if ($qrisPayment->status === 'pending')
                            @if ($qrDataUri)
                                <img src="{{ $qrDataUri }}" alt="QR Code QRIS" class="img-responsive img-thumbnail center-block" style="max-width: 280px; margin-bottom: 15px;">
                            @else
                                <div class="alert alert-danger text-left" style="word-break: break-all; font-size: 11px;">
                                    QR code gagal ditampilkan. Salin kode berikut dan buka lewat aplikasi e-wallet Anda: {{ $qrisPayment->qr_content }}
                                </div>
                            @endif
                        @endif

                        <p class="text-center" style="margin-top: 10px;">Total pembayaran: <strong>Rp {{ number_format($qrisPayment->amount, 0, ',', '.') }}</strong></p>
                        <p class="text-muted" style="font-size: 12px;">
                            QR ini berlaku sampai {{ $qrisPayment->expires_at->format('H:i') }}.
                            Jangan tutup halaman ini, status akan terupdate otomatis begitu pembayaran diterima.
                        </p>

                        <a href="{{ route('order.cart') }}" class="btn btn-outline-primary">Batalkan &amp; kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var isPending = @json($qrisPayment->status === 'pending');
        var statusUrl = @json(route('order.qris-gdc.status', $qrisPayment->reference_no));
        var redirectUrl = @json(route('order.history'));
        var badge = document.getElementById('qris-status-badge');

        if (!isPending) {
            return;
        }

        function setBadge(className, text) {
            badge.className = 'alert ' + className;
            badge.textContent = text;
        }

        function poll() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.status === 'paid') {
                        setBadge('alert-success', 'Pembayaran berhasil! Mengalihkan...');
                        setTimeout(function () { window.location.href = redirectUrl; }, 1200);
                        return;
                    }

                    if (data.status === 'expired' || data.status === 'failed') {
                        setBadge('alert-danger', 'QR sudah kedaluwarsa atau pembayaran gagal. Silakan kembali dan coba lagi.');
                        return;
                    }

                    setTimeout(poll, 3000);
                })
                .catch(function () { setTimeout(poll, 3000); });
        }

        setTimeout(poll, 3000);
    })();
</script>
@endsection
