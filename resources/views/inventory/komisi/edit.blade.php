@extends('inventory.layouts.app')

@section('header', 'Edit Komisi')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Komisi</h3>
        </div>
        <form action="{{ route('inventory.komisi.update', $barang->id_barang) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" class="form-control" value="{{ $barang->nm_barang }}" readonly>
                </div>
                <div class="form-group">
                    <label>Harga Beli</label>
                    <input type="text" class="form-control" value="{{ number_format($barang->hrgsat_barang, 0, ',', '.') }}" readonly>
                </div>
                <div class="form-group">
                    <label>Harga Jual</label>
                    <input type="text" class="form-control" value="{{ number_format($barang->hrgjual_barang, 0, ',', '.') }}" readonly>
                </div>
                <div class="form-group">
                    <label>Metode Komisi</label>
                    <select name="metode" class="form-control" required>
                        <option value="nominal">Nominal</option>
                        <option value="persentase">Persentase (dari Harga Beli)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Komisi *</label>
                    <input type="number" min="0" step="1" name="komisi" class="form-control" required
                        value="{{ old('komisi', $barang->komisi) }}">
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.komisi.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
