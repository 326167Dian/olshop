@extends('backend.layouts.app')

@section('title', 'Edit Testimoni')
@section('header', 'Edit Testimoni')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h3 class="card-title"><i class="fas fa-edit me-2"></i> Edit Testimoni</h3>
            </div>

            <form action="{{ route('backend.testimoni.update', $testimoni->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="form-group">
                        <label>Pelanggan</label>
                        <input type="text" class="form-control" value="{{ $testimoni->user->name ?? '-' }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <select name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ old('rating', $testimoni->rating) == $i ? 'selected' : '' }}>
                                    {{ str_repeat('★', $i) . str_repeat('☆', 5 - $i) }} ({{ $i }})
                                </option>
                            @endfor
                        </select>
                        @error('rating')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="isi">Isi Testimoni</label>
                        <textarea name="isi" class="form-control @error('isi') is-invalid @enderror" rows="6"
                            id="isi" maxlength="1000">{{ old('isi', $testimoni->isi) }}</textarea>
                        @error('isi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('backend.testimoni.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection
