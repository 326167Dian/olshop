@extends('inventory.layouts.app')

@section('header', 'Ubah Satuan')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Ubah Satuan</h3>
        </div>
        <form action="{{ route('inventory.satuan.update', $satuan->id_satuan) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nm_satuan">Satuan</label>
                    <input type="text" name="nm_satuan" id="nm_satuan"
                        class="form-control @error('nm_satuan') is-invalid @enderror"
                        value="{{ old('nm_satuan', $satuan->nm_satuan) }}">
                    @error('nm_satuan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <input type="text" name="deskripsi" id="deskripsi"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        value="{{ old('deskripsi', $satuan->deskripsi) }}">
                    @error('deskripsi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.satuan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
