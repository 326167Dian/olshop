@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UBAH TRANSAKSI</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.jurnalkas.jenis.update', $jenisJurnal) }}" class="form-horizontal">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Nama Transaksi</label>
                    <div class="col-sm-6">
                        <input type="text" name="nm_jurnal" class="form-control" value="{{ $jenisJurnal->nm_jurnal }}" autocomplete="off">
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

    <button type="button" class="btn btn-primary" onclick="history.back()">KEMBALI</button>
@endsection
