@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card {{ $tipe == 1 ? 'box-danger' : 'box-info' }}">
        <div class="card-header">
            <h3 class="card-title">{{ $tipe == 1 ? 'TAMBAH PENGELUARAN' : 'INPUT PEMASUKAN' }}</h3>
        </div>
        <div class="card-body">
            @include('inventory.jurnalkas.partials.nav')
            <br><br>

            <form method="POST" action="{{ route('inventory.jurnalkas.store') }}" class="form-horizontal">
                @csrf
                <input type="hidden" name="tipe" value="{{ $tipe }}">

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Jenis Transaksi</label>
                    <div class="col-sm-6">
                        <select name="idjenis" class="form-control" required>
                            @foreach ($jenisList as $j)
                                <option value="{{ $j->idjenis }}">{{ $j->nm_jurnal }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Keterangan Detail</label>
                    <div class="col-sm-6">
                        <input type="text" name="ket" class="form-control" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Cara Bayar</label>
                    <div class="col-sm-6">
                        <select name="carabayar" class="form-control" required>
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Nilai Transaksi</label>
                    <div class="col-sm-6">
                        <input type="number" name="nilai" class="form-control" required min="0" autocomplete="off">
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
@endsection
