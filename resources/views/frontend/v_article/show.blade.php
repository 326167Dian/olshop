@extends('frontend.layouts.index')
@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <!-- Artikel -->
            <div class="col-md-10 col-md-offset-1">
                <div class="product product-single" style="box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 25px;">
                    <!-- Thumbnail -->
                    <div class="product mb-4 text-center">
                        <img src="{{ $article->thumbnail ? asset('storage/' . $article->thumbnail) : asset('images/default.png') }}"
                            alt="{{ $article->title }}"
                            style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 4px;">
                    </div><br>

                    <!-- Judul -->
                    <h2 class="product-price" style="font-size: 28px; font-weight: bold;">
                        {{ $article->title }}
                    </h2>

                    <!-- Tanggal -->
                    <p style="color: #777;">
                        Dipublikasikan pada: {{ $article->created_at->translatedFormat('d F Y') }}
                    </p>

                    <!-- Konten -->
                    <div class="article-content mt-4" style="line-height: 1.8; font-size: 16px;">
                        {!! $article->content !!}
                    </div>

                    <!-- Tombol kembali -->
                    <div class="mt-5">
                        <a href="{{ route('artikel.all') }}" class="main-btn">
                            Kembali ke Daftar Artikel
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Artikel -->
        </div>
    </div>
</div>
<!-- /SECTION -->
@endsection