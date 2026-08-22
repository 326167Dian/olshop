@extends('frontend.layouts.index')

@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h2 class="title">Daftar Artikel</h2>
                </div>
                <div class="row">
                    @foreach ($articles as $article)
                    <div class="col-md-4 col-sm-6 col-xs-12" data-aos="fade-up">
                        <div class="product product-single d-flex flex-column shadow"
                            style="height: 100%; display: flex; border-radius: 8px; overflow: hidden; transition: 0.3s; background-color: #F6F7F8;">
                            <div class="product-thumb" style="overflow: hidden;">
                                <a href="{{ route('article.show', $article->slug) }}">
                                    <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}"
                                        style="height: 200px; width: 100%; object-fit: cover; border-radius: 8px 8px 0 0;">
                                </a>
                            </div>
                            <div class="product-body d-flex flex-column" style="flex: 1; padding: 15px;">
                                <h3 class="atikel" style="min-height: 60px;">
                                    {{ Str::limit($article->title, 60) }}
                                </h3>
                                <p class="product-name" style="flex: 1;">
                                    {{ Str::limit(strip_tags($article->content), 100) }}
                                </p>
                                <a href="{{ route('article.show', $article->slug) }}" class="primary-btn btn-sm mt-auto"
                                    style="border-radius: 6px;">
                                    Baca Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="text-center mt-4">
                    {{ $articles->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /SECTION -->
@endsection