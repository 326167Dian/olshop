@extends('inventory.layouts.app')

@section('header', 'Laporan Dekat Expired')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Laporan Data Batch</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.lapbatch.tampil') }}" target="_blank" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Tanggal Expired From</label>
                    <input type="date" name="tgl_awal" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Expired To</label>
                    <input type="date" name="tgl_akhir" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Tampil</button>
                </div>
            </form>
        </div>
    </div>
@endsection
