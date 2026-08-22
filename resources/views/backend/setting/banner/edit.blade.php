@extends('backend.layouts.app')

@section('title', 'Edit Banner')

@section('header', 'Form Edit Banner')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Edit Banner</h3>
            </div>

            <form action="{{ route('banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', $banner->status) == 'active' ? 'selected' : ''
                                }}>Active</option>
                            <option value="inactive" {{ old('status', $banner->status) == 'inactive' ? 'selected' : ''
                                }}>Inactive</option>
                        </select>
                        @error('status')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="judul">Nama Banner</label>
                        <input type="text" name="judul" id="judul"
                            class="form-control @error('judul') is-invalid @enderror" placeholder="Masukkan nama banner"
                            value="{{ old('judul', $banner->judul) }}">
                        @error('judul')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan deskripsi banner">{{ old('deskripsi', $banner->deskripsi) }}</textarea>
                        @error('deskripsi')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto Banner</label>
                        <input type="file" name="foto" id="foto"
                            class="form-control @error('foto') is-invalid @enderror">
                        @error('foto')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror

                        @if($banner->foto)
                        <div class="mt-2">
                            <p>Foto Saat Ini:</p>
                            <img src="{{ asset('storage/' . $banner->foto) }}" alt="Banner Image" width="150">
                        </div>
                        @endif
                    </div>

                </div>

                <div class="card-footer">
                    <a href="{{ route('banner.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection