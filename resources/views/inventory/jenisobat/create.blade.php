@extends('inventory.layouts.app')

@section('header', 'Tambah Jenis Obat')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Jenis Obat & Rak Obat</h3>
        </div>
        <form action="{{ route('inventory.jenisobat.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="jenisobat">Jenis Obat & Rak Obat</label>
                    <input type="text" name="jenisobat" id="jenisobat"
                        class="form-control @error('jenisobat') is-invalid @enderror"
                        value="{{ old('jenisobat') }}">
                    @error('jenisobat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="ket">Deskripsi</label>
                    <input type="text" name="ket" id="ket"
                        class="form-control @error('ket') is-invalid @enderror"
                        value="{{ old('ket') }}">
                    @error('ket') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.jenisobat.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
