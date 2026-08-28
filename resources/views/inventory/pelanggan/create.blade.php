@extends('inventory.layouts.app')

@section('header', 'Tambah Pelanggan')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Pelanggan</h3>
        </div>
        <form action="{{ route('inventory.pelanggan.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="nm_pelanggan">Nama Pelanggan</label>
                        <input type="text" name="nm_pelanggan" id="nm_pelanggan"
                            class="form-control @error('nm_pelanggan') is-invalid @enderror"
                            value="{{ old('nm_pelanggan') }}">
                        @error('nm_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin"
                            class="form-control @error('jenis_kelamin') is-invalid @enderror">
                            <option value="" selected disabled>- Pilih -</option>
                            <option value="PRIA" {{ old('jenis_kelamin') == 'PRIA' ? 'selected' : '' }}>PRIA</option>
                            <option value="WANITA" {{ old('jenis_kelamin') == 'WANITA' ? 'selected' : '' }}>WANITA</option>
                        </select>
                        @error('jenis_kelamin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                            class="form-control @error('tanggal_lahir') is-invalid @enderror"
                            value="{{ old('tanggal_lahir') }}">
                        @error('tanggal_lahir') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="tlp_pelanggan">Telepon</label>
                        <input type="text" name="tlp_pelanggan" id="tlp_pelanggan"
                            class="form-control @error('tlp_pelanggan') is-invalid @enderror"
                            value="{{ old('tlp_pelanggan') }}">
                        @error('tlp_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="alamat_pelanggan">Alamat</label>
                        <textarea name="alamat_pelanggan" id="alamat_pelanggan" rows="3"
                            class="form-control @error('alamat_pelanggan') is-invalid @enderror">{{ old('alamat_pelanggan') }}</textarea>
                        @error('alamat_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="ket_pelanggan">Keterangan</label>
                        <textarea name="ket_pelanggan" id="ket_pelanggan" rows="3"
                            class="form-control @error('ket_pelanggan') is-invalid @enderror">{{ old('ket_pelanggan') }}</textarea>
                        @error('ket_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pelanggan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
