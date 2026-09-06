@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TAMBAH JENIS TRANSAKSI</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.jurnalkas.jenis.store') }}" class="form-horizontal">
                @csrf

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Arus Kas</label>
                    <div class="col-sm-6">
                        <select name="tipe" class="form-control" required>
                            <option value="1">KELUAR KAS</option>
                            <option value="2">MASUK KAS</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Nama Transaksi</label>
                    <div class="col-sm-6">
                        <input type="text" name="nm_jurnal" class="form-control" required value="{{ old('nm_jurnal') }}" autocomplete="off">
                        @error('nm_jurnal')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-5">
                        <button type="submit" class="btn btn-primary">SIMPAN</button>
                        <button type="button" class="btn btn-danger" onclick="history.back()">BATAL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-primary" onclick="history.back()">KEMBALI</button>
        <a class="btn btn-success btn-flat" href="{{ route('inventory.jurnalkas.index') }}">HOME</a>
    </div>
@endsection
