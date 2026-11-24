@extends('backend.layouts.app')

@section('title', 'SEO')
@section('header', 'Pengaturan SEO -- ' . $label)

@section('content')
<section class="content">
    <div class="container-fluid">
        <a href="{{ route('seo-meta.index', ['type' => $type]) }}" class="btn btn-sm btn-secondary mb-3">Kembali</a>

        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Override SEO -- {{ $autoTitle }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('seo-meta.update', ['type' => $type, 'id' => $model->getKey()]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Judul (meta title)</label>
                                <input type="text" name="meta_title" class="form-control"
                                    value="{{ old('meta_title', $override->meta_title ?? '') }}"
                                    placeholder="{{ $autoTitle }}">
                                <small class="text-muted">Kosongkan untuk pakai otomatis: "{{ $autoTitle }}"</small>
                                @error('meta_title')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi (meta description)</label>
                                <textarea name="meta_description" class="form-control" rows="3"
                                    placeholder="{{ $autoDescription }}">{{ old('meta_description', $override->meta_description ?? '') }}</textarea>
                                <small class="text-muted">Kosongkan untuk pakai otomatis: "{{ $autoDescription }}"</small>
                                @error('meta_description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gambar Share (Open Graph)</label>
                                @if ($override?->og_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $override->og_image) }}" width="150">
                                    </div>
                                @elseif ($autoImage)
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Gambar otomatis saat ini:</small>
                                        <img src="{{ $autoImage }}" width="150">
                                    </div>
                                @endif
                                <input type="file" name="og_image" class="form-control" accept="image/*">
                                <small class="text-muted">Kosongkan untuk pakai gambar otomatis di atas.</small>
                                @error('og_image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input type="hidden" name="noindex" value="0">
                                <input class="form-check-input" type="checkbox" name="noindex" value="1"
                                    id="noindex" {{ old('noindex', $override->noindex ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="noindex">
                                    Sembunyikan dari Google (noindex) -- halaman tetap bisa dibuka pengunjung,
                                    cuma tidak muncul di hasil pencarian.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>

                        @if ($override)
                            <form action="{{ route('seo-meta.destroy', ['type' => $type, 'id' => $model->getKey()]) }}"
                                method="POST" class="d-inline mt-2"
                                onsubmit="return confirm('Hapus override ini? Halaman akan kembali memakai nilai otomatis.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm mt-2">Hapus Override (kembali ke otomatis)</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Nilai Otomatis Saat Ini</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Judul:</strong> {{ $autoTitle }}</p>
                        <p class="mb-1"><strong>Deskripsi:</strong> {{ $autoDescription }}</p>
                        <p class="mb-1"><strong>URL:</strong> <a href="{{ $autoUrl }}" target="_blank">{{ $autoUrl }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
