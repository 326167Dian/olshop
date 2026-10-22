@extends('frontend.layouts.index')

@section('content')
<!-- SECTION -->
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="section-title">
                    <h2 class="title">{{ $testimoni ? 'Edit Testimoni Saya' : 'Tulis Testimoni' }}</h2>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (!$eligible)
                    <div class="alert alert-warning">
                        Testimoni hanya bisa ditulis oleh pelanggan yang pernah berbelanja di toko online ini.
                    </div>
                    <a href="{{ route('testimoni.all') }}" class="primary-btn btn-sm" style="border-radius: 6px;">
                        Kembali ke Daftar Testimoni
                    </a>
                @else
                    <form action="{{ route('customer.testimoni.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Rating Kepuasan</label>
                            <div class="star-rating">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}"
                                        {{ (int) old('rating', $testimoni->rating ?? 0) === $i ? 'checked' : '' }}>
                                    <label for="star{{ $i }}">★</label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="isi">Tulisan Testimoni</label>
                            <textarea name="isi" id="isi" rows="6" maxlength="1000"
                                class="form-control @error('isi') is-invalid @enderror"
                                placeholder="Ceritakan pengalaman belanja Anda di sini...">{{ old('isi', $testimoni->isi ?? '') }}</textarea>
                            @error('isi')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="primary-btn" style="border-radius: 6px;">
                            {{ $testimoni ? 'Perbarui Testimoni' : 'Kirim Testimoni' }}
                        </button>
                        <a href="{{ route('testimoni.all') }}" class="btn btn-link">Batal</a>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- /SECTION -->

<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        font-size: 2.2rem;
        line-height: 1;
        color: #ccc;
        cursor: pointer;
        padding: 0 2px;
    }

    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #FFC107;
    }
</style>
@endsection
