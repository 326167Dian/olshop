@extends('inventory.layouts.app')

@section('header', 'Ubah Jenis Pembayaran')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Ubah Jenis Pembayaran</h3>
        </div>
        <form action="{{ route('inventory.carabayar.update', $carabayar->id_carabayar) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nm_carabayar">Jenis</label>
                    <input type="text" name="nm_carabayar" id="nm_carabayar"
                        class="form-control @error('nm_carabayar') is-invalid @enderror"
                        value="{{ old('nm_carabayar', $carabayar->nm_carabayar) }}">
                    @error('nm_carabayar') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.carabayar.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
