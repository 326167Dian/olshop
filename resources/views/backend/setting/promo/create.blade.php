@extends('backend.layouts.app')

@section('title', 'Tambah Promo')

@section('header', 'Form Tambah Promo')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Promo</h3>
            </div>

            <form action="{{ route('promo.store') }}" method="POST">
                @csrf
                <div class="card-body">

                    <div class="form-group">
                        <label for="nama_promo">Nama Promo</label>
                        <input type="text" name="nama_promo" id="nama_promo"
                            class="form-control @error('nama_promo') is-invalid @enderror"
                            placeholder="Masukkan nama promo" value="{{ old('nama_promo') }}">
                        @error('nama_promo')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tanggal_awal">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal"
                            class="form-control @error('tanggal_awal') is-invalid @enderror"
                            value="{{ old('tanggal_awal') }}">
                        @error('tanggal_awal')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir"
                            class="form-control @error('tanggal_akhir') is-invalid @enderror"
                            value="{{ old('tanggal_akhir') }}">
                        @error('tanggal_akhir')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nilai_diskon">Nilai Diskon (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="nilai_diskon" id="nilai_diskon"
                            class="form-control @error('nilai_diskon') is-invalid @enderror"
                            placeholder="Masukkan nilai diskon" value="{{ old('nilai_diskon') }}">
                        @error('nilai_diskon')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="card-footer">
                    <a href="{{ route('promo.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection
