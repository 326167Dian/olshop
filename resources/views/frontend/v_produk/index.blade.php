@extends('frontend.layouts.index')
@section('content')
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="order-summary clearfix">
                        <div class="section-title">
                            <h2 class="title">Seluruh Produk</h2>
                        </div>
                        {{-- Alert Success --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <strong>{{ session('success') }}</strong>
                            </div>
                        @endif

                        <div class="col-md-12 mb-4">
                            <form action="{{ route('produk.search') }}" method="GET">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="flex-grow-1">
                                        <input type="text" name="q" class="form-control"
                                            placeholder="Cari produk..." value="{{ request('q') }}">
                                        <br>
                                        <button type="submit" class="btn btn-success w-100 w-sm-auto">Cari</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            @forelse ($produk as $item)
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="product product-single product-grid product-hot" data-aos="fade-up"
                                        data-aos-duration="600">
                                        <div class="product-thumb">
                                            @if ($item->diskon > 0)
                                                <div class="product-label">
                                                    <span class="sale">-{{ $item->diskon }}%</span>
                                                </div>
                                            @elseif ($item->jenisobat == 'XXXX')
                                                <div class="product-label">
                                                    <span class="resep">Butuh Resep Obat</span>
                                                </div>
                                            @endif
                                            <a href="{{ route('produk.detail', $item->id_barang) }}">
                                                <button class="main-btn quick-view"><i class="fa fa-search-plus"></i>
                                                    Detail</button>
                                            </a>
                                            <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/default.png') }}"
                                                alt=""
                                                >
                                        </div>

                                        <div class="product-body">
                                            <h3 class="product-price">
                                                @if ($item->diskon > 0)
                                                    Rp.{{ number_format(round($item->hrgjual_barang2 - ($item->hrgjual_barang2 * $item->diskon) / 100), 0, ',', '.') }}<small style="font-weight: bold">/{{ $item->sat_barang }}</small>
                                                    <del
                                                        class="product-old-price">Rp.{{ number_format($item->hrgjual_barang2, 0, ',', '.') }}</del>
                                                @else
                                                    Rp.{{ number_format($item->hrgjual_barang2, 0, ',', '.') }}<small style="font-weight: bold">/{{ $item->sat_barang }}</small>
                                                @endif
                                            </h3>
                                            <div class="product-rating">
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star-o empty"></i>
                                            </div>
                                            <h2 class="product-name"><a
                                                    href="#">{{ Str::limit($item->nm_barang, 34) }}</a></h2>
                                            <div class="product-btns d-flex align-items-center justify-content-between">
                                                @if ($item->jenisobat == 'XXXXX')
                                                    {{-- Tombol untuk jenis ETC1: Membuka Modal Konfirmasi (Bootstrap 3) --}}
                                                    <button type="button" class="primary-btn add-to-cart"
                                                        data-toggle="modal" data-target="#whatsappConfirmModal"
                                                        data-item-name="{{ $item->nm_barang }}" {{-- Menggunakan nm_barang sesuai kode Anda --}}
                                                        data-item-id="{{ $item->id_barang }}">
                                                        <i class="fa fa-shopping-cart"></i> Pesan (Resep)
                                                    </button>
                                                @else
                                                    {{-- Tombol untuk jenis NON-ETC1: Tambah ke Keranjang Biasa --}}
                                                    <form action="{{ route('order.addToCart', $item->id_barang) }}"
                                                        method="post" style="display: inline-block;">
                                                        @csrf
                                                        <button type="submit" class="primary-btn add-to-cart">
                                                            <i class="fa fa-shopping-cart"></i> Add to cart
                                                        </button>
                                                        <input type="hidden" name="redirect" value="0">
                                                    </form>
                                                @endif
                                                <!-- Tambahkan ini agar bintang bisa muncul di samping tombol saat responsive -->
                                                <div class="d-inline-flex ms-2 responsive-rating">
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star"></i>
                                                    <i class="fa fa-star-o empty"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-12">
                                    <p class="text-center">Produk tidak ditemukan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    {{ $produk->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

        <div class="modal fade" id="whatsappConfirmModal" tabindex="-1" role="dialog"
            aria-labelledby="whatsappConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="whatsappConfirmModalLabel" style="color: #333; font-weight: bold;">
                            <i class="fa fa-info-circle" style="margin-right: 8px;"></i> Informasi Penting: Obat Resep
                        </h4>
                    </div>
                    <div class="modal-body" style="font-size: 16px; line-height: 1.6;">
                        <p>Anda akan memesan obat<strong id="modalItemName"
                                style="color: #d9534f;"></strong>, yang
                            tergolong Obat Keras</p>
                        <p>Obat ini memerlukan resep dokter untuk pembelian.</p>
                        <p
                            style="background-color: #fcf8e3; border-left: 5px solid #8a6d3b; padding: 10px; margin-top: 15px;">
                            <i class="fa fa-exclamation-triangle" style="margin-right: 5px; color: #8a6d3b;"></i>
                            Kami akan mengarahkan Anda ke WhatsApp kami untuk konsultasi lebih lanjut dan proses
                            **pengiriman resep**.
                        </p>
                        <p style="margin-top: 20px; font-weight: bold;">Apakah Anda bersedia melanjutkan ke WhatsApp
                            sekarang?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <i class="fa fa-times-circle" style="margin-right: 5px;"></i> Batal
                        </button>
                        <button type="button" class="btn btn-primary" id="confirmToWhatsappBtn"
                            style="background-color: #25D366; border-color: #25D366;">
                            <i class="fa fa-whatsapp" style="margin-right: 5px;"></i> Lanjutkan ke WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(function() {
                var maxHeight = 0;
                $('.product').each(function() {
                    var h = $(this).outerHeight();
                    if (h > maxHeight) maxHeight = h;
                });
                $('.product').css('min-height', maxHeight + 'px');
            });
        </script>

        {{-- JavaScript harus ditempatkan di sini, SETELAH SEMUA ELEMEN HTML DAN SETELAH JQUERY & BOOTSTRAP JS TERLOAD --}}
        <script>
            $(document).ready(function() {
                const whatsappConfirmModal = $('#whatsappConfirmModal');
                const confirmToWhatsappBtn = $('#confirmToWhatsappBtn');
                const modalItemNameSpan = $('#modalItemName');

                let currentItemId = null;

                whatsappConfirmModal.on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const itemName = button.data('item-name');
                    currentItemId = button.data('item-id');

                    modalItemNameSpan.text(itemName);
                });

                confirmToWhatsappBtn.on('click', function() {
                    if (currentItemId) {
                        const itemName = modalItemNameSpan.text();

                        // PERBAIKI BARIS INI: Hapus titik koma di tengah string
                        const phoneNumber = '+6281298780765'; // GANTI DENGAN NOMOR WA ANDA YANG BENAR

                        const message =
                            `Halo, saya ingin bertanya tentang obat ${itemName} (ID: ${currentItemId}). Apakah obat ini tersedia dan bagaimana cara memesannya dengan resep?`;
                        const whatsappUrl =
                            `https://api.whatsapp.com/send?phone=${phoneNumber}&text=${encodeURIComponent(message)}`;

                        window.open(whatsappUrl, '_blank');

                        whatsappConfirmModal.modal('hide');
                    }
                });
            });
        </script>
    @endsection
