@extends('backend.layouts.app')

@section('title', 'Edit Lokasi Antar')

@section('header', 'Form Edit Lokasi Antar')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Edit Lokasi Antar</h3>
            </div>

            <form action="{{ route('lokasi-antar.update', $lokasiAntar->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="jarak_min">Jarak Min (km)</label>
                            <input type="number" step="0.1" min="0" max="5" name="jarak_min" id="jarak_min"
                                class="form-control @error('jarak_min') is-invalid @enderror"
                                value="{{ old('jarak_min', $lokasiAntar->jarak_min) }}">
                            @error('jarak_min')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label for="jarak_max">Jarak Maks (km)</label>
                            <input type="number" step="0.1" min="0" max="5" name="jarak_max" id="jarak_max"
                                class="form-control @error('jarak_max') is-invalid @enderror"
                                value="{{ old('jarak_max', $lokasiAntar->jarak_max) }}">
                            @error('jarak_max')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="biaya_antar">Biaya Antar Reguler (Rp)</label>
                        <input type="number" step="1" min="0" name="biaya_antar" id="biaya_antar"
                            class="form-control @error('biaya_antar') is-invalid @enderror"
                            placeholder="Masukkan biaya antar"
                            value="{{ old('biaya_antar', $lokasiAntar->biaya_antar) }}">
                        @error('biaya_antar')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Tarif Express otomatis 2x nilai ini, tidak perlu diisi terpisah.</small>
                    </div>

                </div>

                <div class="card-footer">
                    <a href="{{ route('lokasi-antar.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection
