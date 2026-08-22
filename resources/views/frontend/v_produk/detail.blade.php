@extends('frontend.layouts.index')
@section('content')
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-title">
                        <h2 class="title">{{ $judul }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section"> {{-- Added a new section for product details for better structure --}}
        <div class="container">
            <div class="row product product-details clearfix"> {{-- The product details are now within a Bootstrap row --}}
                {{-- Product Image Column --}}
                <div class="col-md-6">
                    <div id="product-main"">
                        <div class=" product-view">
                            <img src="{{ asset('storage/' . ($produks->image ?? 'default.png')) }}"
                                class="img-fluid img-thumbnail product product-single product-hot"
                                style="height: 423px; width: auto; object-fit: contain;" alt="Gambar Produk">
                        </div>
                    </div>
                    {{-- This div is likely for product thumbnails or additional views --}}
                    <div id="product-view">
                        {{-- Content for product thumbnails/carousel goes here --}}
                    </div>
                </div>
                {{-- /Product Image Column --}}

                {{-- Product Info Column --}}
                <div class="col-md-6">
                    <div class="product-body">
                        <div class="product-label">
                            <span>Kategori</span>
                            <span class="sale">{{ $produks->sat_barang ?? '-' }}</span>
                        </div>
                        <h2 class="product-name">{{ $produks->nm_barang }}</h2>
                        <h3 class="product">
                            @if ($produks->diskon > 0)
                                Rp.{{ number_format(round($produks->hrgjual_barang2 - ($produks->hrgjual_barang2 * $produks->diskon) / 100), 0, ',', '.') }}<small
                                    style="font-weight: bold">/{{ $produks->sat_barang }}</small>
                                <del
                                    class="product-old-price">Rp.{{ number_format($produks->hrgjual_barang2, 0, ',', '.') }}</del>
                            @else
                                Rp.{{ number_format($produks->hrgjual_barang2, 0, ',', '.') }}<small
                                    style="font-weight: bold">/{{ $produks->sat_barang }}</small>
                            @endif
                        </h3>
                        <p>{!! $produks->indikasi !!}</p>

                        <div class="product-options">
                            <ul class="size-option">
                                <li><span class="text-uppercase">Stok:</span></li>
                                <li>{{ $produks->stok_barang }}</li> {{-- Wrapped stok_barang in li for consistency --}}
                            </ul>
                        </div>

                        <div class="product-btns">
                            <form action="{{ route('order.addToCart', $produks->id_barang) }}" method="post"
                                style="display: inline-block;" title="Pesan Ke Aplikasi">
                                @csrf
                                <button type="submit" class="primary-btn"><i class="fa fa-shopping-cart"></i>
                                    Add to cart</button>
                                <input type="hidden" name="redirect" value="1">
                            </form>
                            </form>
                            <a href="{{ route('produk.all') }}">
                                <button type="button" class="primary-btn"> {{-- Changed type to "button" as it's
                            a link --}}
                                    Kembali
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                {{-- /Product Info Column --}}
            </div>
            <br> {{-- Add spacing if needed, but consider using CSS margin/padding for better control --}}
        </div>
    </div>
@endsection
