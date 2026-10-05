@extends('frontend.layouts.index')
@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <!-- Video -->
            <div class="col-md-10 col-md-offset-1">
                <div class="product product-single" style="box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 25px;">
                    <!-- Player -->
                    <div class="product mb-4 text-center">
                        @if ($video->embed_url)
                        <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:4px;">
                            <iframe src="{{ $video->embed_url }}" title="{{ $video->judul }}"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                        @else
                        <img src="{{ asset('images/default.png') }}" alt="{{ $video->judul }}" style="width:100%; max-height:400px; object-fit:cover;">
                        @endif
                    </div><br>

                    <!-- Judul -->
                    <h2 class="product-price" style="font-size: 28px; font-weight: bold;">
                        {{ $video->judul }}
                    </h2>

                    <!-- Tanggal -->
                    <p style="color: #777;">
                        Dipublikasikan pada: {{ $video->created_at->translatedFormat('d F Y') }}
                    </p>

                    <!-- Tombol kembali -->
                    <div class="mt-5">
                        <a href="{{ route('video-edukasi.all') }}" class="main-btn">
                            Kembali ke Daftar Video Edukasi
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Video -->
        </div>
    </div>
</div>
<!-- /SECTION -->
@endsection
