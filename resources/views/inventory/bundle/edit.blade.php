@extends('inventory.layouts.app')

@section('header', 'Bundle/Paket Produk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ubah Paket Produk</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.bundle.update', $bundle) }}" id="form-bundle">
                @csrf
                @include('inventory.bundle.partials.form', ['bundle' => $bundle])
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @include('inventory.bundle.partials.form-script')
@endpush
