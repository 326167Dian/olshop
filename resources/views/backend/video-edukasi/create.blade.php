@extends('backend.layouts.app')

@section('title', 'Tambah Video Edukasi')
@section('header', 'Tambah Video Edukasi')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-video me-2"></i> Tambah Video Edukasi Baru</h3>
            </div>

            <form action="{{ route('video-edukasi.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    {{-- Judul --}}
                    <div class="form-group">
                        <label for="judul">Judul Video</label>
                        <input type="text" name="judul" id="judul"
                            class="form-control @error('judul') is-invalid @enderror"
                            placeholder="Masukkan judul video" value="{{ old('judul') }}">
                        @error('judul')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="form-group">
                        <input type="hidden" name="slug" class="form-control" id="slug"
                            placeholder="contoh-judul-video" required readonly>
                    </div>

                    {{-- Link YouTube --}}
                    <div class="form-group">
                        <label for="youtube_url">Link YouTube</label>
                        <input type="url" name="youtube_url" id="youtube_url"
                            class="form-control @error('youtube_url') is-invalid @enderror"
                            placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx"
                            value="{{ old('youtube_url') }}">
                        <small class="form-text text-muted">Tempel link video YouTube apa saja (watch, youtu.be, atau shorts).</small>
                        @error('youtube_url')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
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
                    <a href="{{ route('video-edukasi.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Slug generator otomatis dari judul
        const judulInput = document.getElementById('judul');
        const slugInput = document.getElementById('slug');

        if (judulInput && slugInput) {
            judulInput.addEventListener('keyup', function () {
                let slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                slugInput.value = slug;
            });
        }
    });
</script>
@endpush
