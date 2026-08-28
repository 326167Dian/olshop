@extends('inventory.layouts.app')

@section('header', 'Koreksi Cek Darah')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Koreksi Cek Darah</h3>
        </div>
        <form action="{{ route('inventory.cekdarah.update', $cekdarah->id_cekdarah) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Pasien</label>
                    <input type="text" class="form-control" value="{{ $cekdarah->pelanggan->nm_pelanggan ?? '-' }}" disabled>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="gula">Glukosa</label>
                        <input type="text" name="gula" id="gula"
                            class="form-control @error('gula') is-invalid @enderror"
                            value="{{ old('gula', $cekdarah->gula) }}">
                        @error('gula') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="asamurat">Asam Urat</label>
                        <input type="text" name="asamurat" id="asamurat"
                            class="form-control @error('asamurat') is-invalid @enderror"
                            value="{{ old('asamurat', $cekdarah->asamurat) }}">
                        @error('asamurat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="kolesterol">Kolesterol</label>
                        <input type="text" name="kolesterol" id="kolesterol"
                            class="form-control @error('kolesterol') is-invalid @enderror"
                            value="{{ old('kolesterol', $cekdarah->kolesterol) }}">
                        @error('kolesterol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tensi">Tensi</label>
                        <input type="text" name="tensi" id="tensi"
                            class="form-control @error('tensi') is-invalid @enderror"
                            value="{{ old('tensi', $cekdarah->tensi) }}">
                        @error('tensi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.cekdarah.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
