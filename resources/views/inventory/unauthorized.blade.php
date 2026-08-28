@extends('inventory.layouts.app')

@section('header', 'Akses Ditolak')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="mb-2 text-danger">Anda tidak berhak mengakses halaman ini.</h5>
            <p class="mb-0 text-muted">Hubungi pemilik/admin untuk meminta akses ke modul ini.</p>
        </div>
    </div>
@endsection
