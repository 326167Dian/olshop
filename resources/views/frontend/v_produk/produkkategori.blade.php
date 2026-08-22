@extends('frontend.layouts.index')
@section('content')
<!-- section -->
<div class="section">
    <!-- container -->
    <div class="container">
        <!-- row -->
        <div class="row">
            <!-- section title -->
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="title">{{ $judul ?? 'Seluruh Produk' }}</h2>
                </div>
            </div>
            <!-- section title -->

            <!-- Product Single -->
            @forelse ($produk as $item)
            <div class="col-6 col-sm-4 col-md-3">
                <div class="product product-single product-grid product-hot" data-aos="fade-up" data-aos-duration="600">
                    <div class="product-thumb">
                        @if ($item->diskon > 0)
                        <div class="product-label">
                            <span class="sale">-{{ $item->diskon }}%</span>
                        </div>
                        @elseif ($item->jenisobat == 'ETC1')
                        <div class="product-label">
                            <span class="resep">Butuh Resep Obat</span>
                        </div>
                        @endif
                        <a href="{{ route('produk.detail', $item->id_barang) }}">
                            <button class="main-btn quick-view"><i class="fa fa-search-plus"></i>
                                Detail</button>
                        </a>
                        <img src="{{ asset('storage/' . ($item->image ?? 'default.png')) }}" alt="">
                    </div>

                    <div class="product-body">
                        <h3 class="product-price">
                            @if ($item->diskon > 0)
                            Rp.{{ number_format(round($item->hrgjual_barang2 - ($item->hrgjual_barang2 *
                            $item->diskon) / 100), 0, ',', '.') }}
                            <del class="product-old-price">Rp.{{ number_format($item->hrgjual_barang2, 0,
                                ',',
                                '.') }}</del>
                            @else
                            Rp.{{ number_format($item->hrgjual_barang2, 0, ',', '.') }}
                            @endif
                        </h3>
                        <div class="product-rating">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-o empty"></i>
                        </div>
                        <h2 class="product-name"><a href="#">{{ Str::limit($item->nm_barang, 34) }}</a></h2>
                        <div class="product-btns d-flex align-items-center justify-content-between">
                            @if ($item->jenisobat == 'ETC1')
                            {{-- Tombol untuk jenis ETC1: Membuka Modal Konfirmasi (Bootstrap 3) --}}
                            <button type="button" class="primary-btn add-to-cart" data-toggle="modal"
                                data-target="#whatsappConfirmModal" data-item-name="{{ $item->nm_barang }}" {{--
                                Menggunakan nm_barang sesuai kode Anda --}} data-item-id="{{ $item->id_barang }}">
                                <i class="fa fa-shopping-cart"></i> Pesan (Resep)
                            </button>
                            @else
                            {{-- Tombol untuk jenis NON-ETC1: Tambah ke Keranjang Biasa --}}
                            <form action="{{ route('order.addToCart', $item->id_barang) }}" method="post"
                                style="display: inline-block;">
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

        <!-- Pagination -->
        <div class="text-center">
            {{ $produk->links('pagination::bootstrap-4') }}
        </div>
    </div>
    <!-- /container -->
</div>
<!-- /section -->
@endsection