@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT JURNAL</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.jurnalkas.update', $jurnal) }}" class="form-horizontal">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Jenis Transaksi</label>
                    <div class="col-sm-6">
                        <select name="idjenis" class="form-control" required>
                            @foreach ($jenisList as $j)
                                <option value="{{ $j->idjenis }}" @selected($j->idjenis == $jurnal->idjenis)>{{ $j->nm_jurnal }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Keterangan Detail</label>
                    <div class="col-sm-6">
                        <input type="text" name="ket" class="form-control" required value="{{ $jurnal->ket }}" autocomplete="off">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Cara Bayar</label>
                    <div class="col-sm-6">
                        <select name="carabayar" class="form-control" required>
                            <option value="TUNAI" @selected($jurnal->carabayar === 'TUNAI')>TUNAI</option>
                            <option value="TRANSFER" @selected($jurnal->carabayar === 'TRANSFER')>TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Nilai Pengeluaran</label>
                    <div class="col-sm-6">
                        <input type="number" name="debit" class="form-control" required min="0" value="{{ $jurnal->debit }}" autocomplete="off">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 control-label">Nilai Pemasukan</label>
                    <div class="col-sm-6">
                        <input type="number" name="kredit" class="form-control" required min="0" value="{{ $jurnal->kredit }}" autocomplete="off">
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
