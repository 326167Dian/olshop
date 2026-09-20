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

                    <div class="form-group">
                        <label for="nama_kelurahan">Nama Kelurahan</label>
                        <input type="text" name="nama_kelurahan" id="nama_kelurahan"
                            class="form-control @error('nama_kelurahan') is-invalid @enderror"
                            placeholder="Masukkan nama kelurahan"
                            value="{{ old('nama_kelurahan', $lokasiAntar->nama_kelurahan) }}">
                        @error('nama_kelurahan')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="biaya_antar">Biaya Antar (Rp)</label>
                        <input type="number" step="1" min="0" name="biaya_antar" id="biaya_antar"
                            class="form-control @error('biaya_antar') is-invalid @enderror"
                            placeholder="Masukkan biaya antar"
                            value="{{ old('biaya_antar', $lokasiAntar->biaya_antar) }}">
                        @error('biaya_antar')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
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
