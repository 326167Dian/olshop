@extends('frontend.layouts.index')
@section('content')

<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="order-summary clearfix">
                    <div class="section-title">
                        <h3 class="title">Keranjang Belanja</h3>
                    </div>

                    {{-- Alert Success --}}
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <strong>{{ session('success') }}</strong>
                    </div>
                    @endif

                    {{-- Alert Error --}}
                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <strong>{{ session('error') }}</strong>
                    </div>
                    @endif

                    {{-- Tabel Keranjang --}}
                    @if ($order && $order->orderItems->count() > 0)
                    <div class="visible-md visible-lg">
                        <table class="shopping-cart-table table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th></th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-right"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $totalHarga = 0;
                                @endphp
                                @foreach ($order->orderItems as $item)
                                <tr>
                                    <td class="thumb">
                                        <img src="{{ $item->produk->image ? asset('storage/' . $item->produk->image) : asset('img/default.jpg') }}"
                                            alt="">
                                    </td>
                                    <td class="details">
                                        <a style="font-size: 15px">{{ $item->produk->nm_barang }}</a>
                                    </td>
                                    <td class="price text-center">
                                        <strong>Rp {{ number_format($item->harga, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="qty text-center">
                                        <form action="{{ route('order.updateCart', $item->id) }}" method="POST">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                                max="{{ $item->produk->stok }}" style="width: 35px;"
                                                onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="total text-center">
                                        <strong class="primary-color">
                                            Rp
                                            {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-right">
                                        <form action="{{ route('order.remove', ['id' => $item->produk->id_barang]) }}"
                                            method="POST">
                                            @csrf
                                            <button class="main-btn icon-btn"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- Checkout --}}
                        <form action="#" method="POST">
                            @csrf
                            <div class="pull-right">
                                <button type="button" class="primary-btn" data-toggle="modal"
                                    data-target="#checkoutModal">
                                    Check Out
                                </button>
                            </div>
                        </form>
                    </div>
                    {{-- Mobile Version --}}
                    <div class="visible-xs visible-sm">
                        @php $totalHarga = 0; @endphp
                        @foreach ($order->orderItems as $item)
                        @php
                        $subTotal = $item->harga * $item->quantity;
                        $totalHarga += $subTotal;
                        @endphp
                        <div class="panel panel-default" style="margin-bottom: 10px;">
                            <div class="panel-body">
                                <div class="media">
                                    <div class="media-left">
                                        <img src="{{ $item->produk->image ? asset('storage/' . $item->produk->image) : asset('img/default.jpg') }}"
                                            class="media-object" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="media-body">
                                        <h5 style="font-size: 15px; font-weight: 600;">
                                            {{ $item->produk->nm_barang }}</h5>
                                        <p style="margin: 5px 0;">Harga: <strong>Rp
                                                {{ number_format($item->harga, 0, ',', '.') }}</strong></p>
                                        <p style="margin: 5px 0;">Total: <strong class="text-success">Rp
                                                {{ number_format($subTotal, 0, ',', '.') }}</strong></p>
                                        <form action="{{ route('order.updateCart', $item->id) }}" method="POST">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                                max="{{ $item->produk->stok }}" style="width: 35px;"
                                                onchange="this.form.submit()">
                                        </form>
                                        <form action="{{ route('order.remove', ['id' => $item->produk->id_barang]) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-xs btn-danger pull-right"><i class="fa fa-trash"></i>
                                                Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{-- Total dan Checkout --}}
                        <div class="panel panel-default">
                            <div class="panel-body text-right">
                                <strong>Total: Rp {{ number_format($totalHarga, 0, ',', '.') }}</strong>
                                <br>
                                <button type="button" class="btn btn-primary btn-block" data-toggle="modal"
                                    data-target="#checkoutModal" style="margin-top: 10px;">
                                    Check Out
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <p>Keranjang belanja kosong.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Checkout -->
<div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <form id="checkoutForm" method="POST" action="{{ route('order.selectPickup') }}">
            @csrf
            <div class="modal-content custom-card-modal">
                <div class="modal-header border-0 pb-0"> <button type="button" class="close" data-dismiss="modal"
                        aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center pt-0">
                    <h5 class="modal-title mb-3" id="checkoutModalLabel">Pilih Metode Pengambilan</h5>
                    <p class="text-muted mb-4">Bagaimana Anda ingin menerima pesanan Anda?</p>

                    <div class="d-grid gap-3"> <label class="custom-selection-box">
                            <input type="radio" name="tipe_layanan" value="Dikirim ke alamat" required>
                            <div class="content">
                                <i class="fas fa-truck fa-2x mb-2 text-primary"></i> <span>Dikirim ke Alamat</span>
                            </div>
                        </label>

                        <label class="custom-selection-box">
                            <input type="radio" name="tipe_layanan" value="Ambil di toko" required>
                            <div class="content">
                                <i class="fas fa-store fa-2x mb-2 text-info"></i> <span>Ambil di Toko</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-center"> <button type="button"
                        class="btn btn-outline-secondary rounded-pill me-2" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill">Lanjutkan Checkout</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Custom styles for the new modal design */
    .custom-card-modal {
        border-radius: 1rem;
        /* More pronounced rounded corners */
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        /* Stronger but still soft shadow */
        border: none;
        /* Remove default border */
    }

    .custom-card-modal .modal-header .close {
        padding: 1rem;
        /* More click area for close button */
        margin: -1rem -1rem -1rem auto;
        /* Position close button top-right */
    }

    .custom-card-modal .modal-title {
        font-weight: 700;
        /* Bolder title */
        color: #343a40;
        font-size: 1.5rem;
        /* Larger title */
    }

    .custom-card-modal .text-muted {
        font-size: 0.95rem;
    }

    /* Custom Selection Box for Radio Buttons */
    .custom-selection-box {
        display: block;
        /* Make it block level */
        padding: 1.25rem;
        /* More padding */
        border: 2px solid #e0e0e0;
        /* Subtle border */
        border-radius: 0.75rem;
        /* Rounded corners for the box */
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        position: relative;
        text-align: center;
        background-color: #fff;
    }

    .custom-selection-box:hover {
        border-color: #a8dadc;
        /* Light blue on hover */
        background-color: #f8fcfd;
        /* Very light blue background on hover */
    }

    .custom-selection-box input[type="radio"] {
        /* Hide default radio button */
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .custom-selection-box input[type="radio"]:checked+.content {
        color: #007bff;
        /* Primary text color when checked */
    }

    .custom-selection-box input[type="radio"]:checked+.content .fas {
        color: #007bff !important;
        /* Force icon color to primary when checked */
    }

    .custom-selection-box input[type="radio"]:checked {
        border-color: #007bff;
        /* Primary color border when checked */
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        /* Subtle glow when checked */
    }

    .custom-selection-box input[type="radio"]:checked~.custom-selection-box {
        border-color: #007bff;
        background-color: #e7f3ff;
        /* Light background for checked */
    }

    .custom-selection-box .content {
        display: flex;
        flex-direction: column;
        /* Stack icon and text */
        align-items: center;
        color: #495057;
        /* Default text color */
        font-weight: 500;
    }

    .custom-selection-box .content span {
        margin-top: 0.5rem;
        /* Space between icon and text */
        font-size: 1.1rem;
    }

    /* Buttons */
    .btn-success {
        background-color: #28a745;
        /* Bootstrap success green */
        border-color: #28a745;
        padding: 0.6rem 1.5rem;
        /* More padding for a softer look */
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-1px);
        /* Slight lift on hover */
    }

    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
        transform: translateY(-1px);
    }
</style>


<script>
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const metode = document.querySelector('input[name="tipe_layanan"]:checked').value;
        const form = e.target;

        // selalu submit ke route selectPickup
        form.action = "{{ route('order.selectPickup') }}";

        // tambahkan hidden input supaya server tahu user pilih apa
        let hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tipe_layanan';
        hiddenInput.value = metode;
        form.appendChild(hiddenInput);

        form.submit();
    });

</script>


@endsection