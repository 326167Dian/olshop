@extends('backend.layouts.app')

@section('title', 'Tulis Artikel')
@section('header', 'Tulis Artikel')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-pen me-2"></i> Tulis Artikel Baru</h3>
            </div>

            <form action="{{ route('article.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card-body">
                    {{-- Judul --}}
                    <div class="form-group">
                        <label for="title">Judul Artikel</label>
                        <input type="text" name="title" id="title"
                            class="form-control @error('title') is-invalid @enderror"
                            placeholder="Masukkan judul artikel" value="{{ old('title') }}">
                        @error('title')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="form-group">
                        <input type="hidden" name="slug" class="form-control" id="slug"
                            placeholder="contoh-judul-artikel" required readonly>
                    </div>

                    {{-- Konten --}}
                    <label for="content">Konten Artikel</label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="6"
                        id="content" placeholder="Isi artikel di sini...">{{ old('content') }}</textarea>
                    @error('content')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror

                    {{-- Thumbnail --}}
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control-file" id="thumbnail" accept="image/*">
                        @error('thumbnail')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                            <option value="" {{ old('status')==null ? 'selected' : '' }}>
                                Pilih Status
                            </option>
                            <option value="draft" {{ old('status')=='draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status')=='published' ? 'selected' : '' }}>Published
                            </option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('article.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi CKEditor
        ClassicEditor
            .create(document.querySelector('#content'))
            .catch(error => {
                console.error(error);
            });

        // Slug generator otomatis dari judul
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        if (titleInput && slugInput) {
            titleInput.addEventListener('keyup', function () {
                let slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '') // hilangkan karakter khusus
                    .replace(/\s+/g, '-')         // ganti spasi dengan strip
                    .replace(/-+/g, '-');         // hilangkan duplikat strip
                slugInput.value = slug;
            });
        }
    });
</script>

@endpush