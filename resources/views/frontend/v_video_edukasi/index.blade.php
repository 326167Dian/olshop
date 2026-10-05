@extends('frontend.layouts.index')

@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="title">Video Edukasi</h2>
                </div>
                <div class="row">
                    @foreach ($videos as $video)
                    <div class="col-md-4 col-sm-6 col-xs-12" data-aos="fade-up">
                        <div class="product product-single d-flex flex-column shadow"
                            style="height: 100%; display: flex; border-radius: 8px; overflow: hidden; transition: 0.3s; background-color: #F6F7F8;">
                            <div class="product-thumb" style="overflow: hidden; position: relative;">
                                <a href="{{ route('video-edukasi.show', $video->slug) }}">
                                    <img src="{{ $video->thumbnail_url ?? asset('images/default.png') }}" alt="{{ $video->judul }}"
                                        style="height: 200px; width: 100%; object-fit: cover; border-radius: 8px 8px 0 0;">
                                    <i class="fa fa-play-circle"
                                        style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:48px; color:#fff; text-shadow:0 0 8px rgba(0,0,0,0.6);"></i>
                                </a>
                            </div>
                            <div class="product-body d-flex flex-column" style="flex: 1; padding: 15px;">
                                <h3 class="atikel" style="min-height: 60px;">
                                    {{ Str::limit($video->judul, 60) }}
                                </h3>
                                <a href="{{ route('video-edukasi.show', $video->slug) }}" class="primary-btn btn-sm mt-auto"
                                    style="border-radius: 6px;">
                                    Tonton Video
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="text-center mt-4">
                    {{ $videos->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /SECTION -->
@endsection
