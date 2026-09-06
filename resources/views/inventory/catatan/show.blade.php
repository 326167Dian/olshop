@extends('inventory.layouts.app')

@section('header', 'Catatan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DETAIL CATATAN</h3>
        </div>
        <div class="card-body">
            <div class="catatan-content">{!! $catatan->deskripsi !!}</div>
            <br>
            <button type="button" class="btn btn-primary" onclick="history.back()">KEMBALI</button>
        </div>
    </div>
@endsection
