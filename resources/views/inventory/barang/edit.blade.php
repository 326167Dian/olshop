@extends('inventory.layouts.app')

@section('header', 'Ubah Barang')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Data Barang</h3>
        </div>
        <form action="{{ route('inventory.barang.update', $barang->id_barang) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" class="form-control" value="{{ $barang->kd_barang }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang *</label>
                            <input type="text" name="nm_barang" class="form-control @error('nm_barang') is-invalid @enderror"
                                required value="{{ old('nm_barang', $barang->nm_barang) }}">
                            @error('nm_barang') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Satuan Retail *</label>
                            <select name="sat_barang" class="form-control" required>
                                <option value="{{ $barang->sat_barang }}" selected>{{ $barang->sat_barang }}</option>
                                @foreach ($satuanList as $s)
                                    @if ($s->nm_satuan !== $barang->sat_barang)
                                        <option value="{{ $s->nm_satuan }}">{{ $s->nm_satuan }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Satuan Grosir *</label>
                            <select name="sat_grosir" class="form-control" required>
                                <option value="{{ $barang->sat_grosir }}" selected>{{ $barang->sat_grosir }}</option>
                                @foreach ($satuanList as $s)
                                    @if ($s->nm_satuan !== $barang->sat_grosir)
                                        <option value="{{ $s->nm_satuan }}">{{ $s->nm_satuan }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Rak Obat / Jenis Obat</label>
                            <select name="jenisobat" class="form-control">
                                <option value="{{ $barang->jenisobat }}" selected>{{ $barang->jenisobat }}</option>
                                @foreach ($jenisObatList as $j)
                                    @if ($j->jenisobat !== $barang->jenisobat)
                                        <option value="{{ $j->jenisobat }}">{{ $j->jenisobat }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Konversi *</label>
                            <input type="number" min="0" name="konversi" class="form-control" required
                                value="{{ old('konversi', $barang->konversi) }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Beli Retail *</label>
                            <input type="number" min="0" name="hrgsat_barang" class="form-control" required
                                value="{{ old('hrgsat_barang', $barang->hrgsat_barang) }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Beli Grosir *</label>
                            <input type="number" min="0" name="hrgsat_grosir" class="form-control" required
                                value="{{ old('hrgsat_grosir', $barang->hrgsat_grosir) }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Reguler *</label>
                            <input type="number" min="0" name="hrgjual_barang" class="form-control" required
                                value="{{ old('hrgjual_barang', $barang->hrgjual_barang) }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Resep *</label>
                            <input type="number" min="0" name="hrgjual_barang1" class="form-control" required
                                value="{{ old('hrgjual_barang1', $barang->hrgjual_barang1) }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Marketplace *</label>
                            <input type="number" min="0" name="hrgjual_barang2" class="form-control" required
                                value="{{ old('hrgjual_barang2', $barang->hrgjual_barang2) }}">
                        </div>
                        <div class="form-group">
                            <label>Zat Aktif</label>
                            <textarea name="zataktif" class="form-control" rows="4">{{ old('zataktif', $barang->zataktif) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Komposisi dan Indikasi</label>
                            <textarea name="indikasi" class="form-control" rows="6">{{ old('indikasi', $barang->indikasi) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Komposisi (Keterangan Lain)</label>
                            <textarea name="ket_barang" class="form-control" rows="6">{{ old('ket_barang', $barang->ket_barang) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Dosis / Kekuatan</label>
                            <textarea name="dosis" class="form-control" rows="3">{{ old('dosis', $barang->dosis) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.barang.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
