@extends('inventory.layouts.app')

@section('header', 'Tambah Barang')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Data Barang</h3>
        </div>
        <form action="{{ route('inventory.barang.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" name="kd_barang" class="form-control" autocomplete="off"
                                placeholder="{{ $kodeBarang }}" value="{{ old('kd_barang') }}">
                            <small class="text-muted">Kosongkan untuk kode otomatis: {{ $kodeBarang }}</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang *</label>
                            <input type="text" name="nm_barang" class="form-control @error('nm_barang') is-invalid @enderror"
                                required value="{{ old('nm_barang') }}">
                            @error('nm_barang') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Satuan Retail *</label>
                            <select name="sat_barang" class="form-control" required>
                                <option value="">- Pilih -</option>
                                @foreach ($satuanList as $s)
                                    <option value="{{ $s->nm_satuan }}" {{ old('sat_barang') == $s->nm_satuan ? 'selected' : '' }}>{{ $s->nm_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Satuan Grosir *</label>
                            <select name="sat_grosir" class="form-control" required>
                                <option value="">- Pilih -</option>
                                @foreach ($satuanList as $s)
                                    <option value="{{ $s->nm_satuan }}" {{ old('sat_grosir') == $s->nm_satuan ? 'selected' : '' }}>{{ $s->nm_satuan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Rak Obat / Jenis Obat</label>
                            <select name="jenisobat" class="form-control">
                                <option value="">- Pilih -</option>
                                @foreach ($jenisObatList as $j)
                                    <option value="{{ $j->jenisobat }}" {{ old('jenisobat') == $j->jenisobat ? 'selected' : '' }}>{{ $j->jenisobat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Konversi *</label>
                            <input type="number" min="0" name="konversi" class="form-control" required
                                value="{{ old('konversi') }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Beli Retail *</label>
                            <input type="number" min="0" name="hrgsat_barang" class="form-control" required
                                value="{{ old('hrgsat_barang') }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Beli Grosir *</label>
                            <input type="number" min="0" name="hrgsat_grosir" class="form-control" required
                                value="{{ old('hrgsat_grosir') }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Reguler *</label>
                            <input type="number" min="0" name="hrgjual_barang" class="form-control" required
                                value="{{ old('hrgjual_barang') }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Resep *</label>
                            <input type="number" min="0" name="hrgjual_barang1" class="form-control" required
                                value="{{ old('hrgjual_barang1') }}">
                        </div>
                        <div class="form-group">
                            <label>Harga Jual Marketplace *</label>
                            <input type="number" min="0" name="hrgjual_barang2" class="form-control" required
                                value="{{ old('hrgjual_barang2') }}">
                        </div>
                        <div class="form-group">
                            <label>Zat Aktif</label>
                            <input type="text" name="zataktif" class="form-control" value="{{ old('zataktif') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Komposisi dan Indikasi</label>
                            <textarea name="indikasi" class="form-control" rows="6">{{ old('indikasi') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Keterangan Lain</label>
                            <textarea name="ket_barang" class="form-control" rows="6">{{ old('ket_barang') }}</textarea>
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
