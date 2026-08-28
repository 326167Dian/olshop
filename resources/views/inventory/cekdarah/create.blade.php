@extends('inventory.layouts.app')

@section('header', 'Input Hasil Cek Darah')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Hasil Cek</h3>
        </div>
        <form action="{{ route('inventory.cekdarah.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="id_pelanggan">Nama Pasien</label>
                    <select name="id_pelanggan" id="id_pelanggan"
                        class="form-control @error('id_pelanggan') is-invalid @enderror">
                        <option value="" disabled {{ old('id_pelanggan', $idPelangganTerpilih) == '' ? 'selected' : '' }}>- Pilih -</option>
                        @foreach ($pelangganList as $p)
                            <option value="{{ $p->id_pelanggan }}" {{ old('id_pelanggan', $idPelangganTerpilih) == $p->id_pelanggan ? 'selected' : '' }}>
                                {{ $p->nm_pelanggan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="gula">Glukosa</label>
                        <input type="text" name="gula" id="gula"
                            class="form-control @error('gula') is-invalid @enderror" value="{{ old('gula') }}">
                        @error('gula') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="asamurat">Asam Urat</label>
                        <input type="text" name="asamurat" id="asamurat"
                            class="form-control @error('asamurat') is-invalid @enderror" value="{{ old('asamurat') }}">
                        @error('asamurat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="kolesterol">Kolesterol</label>
                        <input type="text" name="kolesterol" id="kolesterol"
                            class="form-control @error('kolesterol') is-invalid @enderror" value="{{ old('kolesterol') }}">
                        @error('kolesterol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tensi">Tensi</label>
                        <input type="text" name="tensi" id="tensi"
                            class="form-control @error('tensi') is-invalid @enderror" value="{{ old('tensi') }}">
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
