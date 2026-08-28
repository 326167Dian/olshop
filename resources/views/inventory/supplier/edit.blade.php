@extends('inventory.layouts.app')

@section('header', 'Ubah Supplier')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Ubah Supplier</h3>
        </div>
        <form action="{{ route('inventory.supplier.update', $supplier->id_supplier) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nm_supplier">Nama Supplier</label>
                    <input type="text" name="nm_supplier" id="nm_supplier"
                        class="form-control @error('nm_supplier') is-invalid @enderror"
                        value="{{ old('nm_supplier', $supplier->nm_supplier) }}">
                    @error('nm_supplier') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="tlp_supplier">Telepon</label>
                    <input type="text" name="tlp_supplier" id="tlp_supplier" class="form-control"
                        value="{{ old('tlp_supplier', $supplier->tlp_supplier) }}">
                </div>
                <div class="form-group">
                    <label for="alamat_supplier">Alamat</label>
                    <textarea name="alamat_supplier" id="alamat_supplier" class="form-control" rows="3">{{ old('alamat_supplier', $supplier->alamat_supplier) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="ket_supplier">Keterangan</label>
                    <textarea name="ket_supplier" id="ket_supplier" class="form-control" rows="3">{{ old('ket_supplier', $supplier->ket_supplier) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.supplier.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
