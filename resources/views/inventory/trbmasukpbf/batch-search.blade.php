@extends('inventory.layouts.app')

@section('header', 'Cari No. Batch')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Cari No. Batch</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.trbmasukpbf.batch-search.result') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">No. Batch</label>
                    <input type="text" name="no_batch" class="form-control" required autofocus>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
            <a href="{{ route('inventory.trbmasukpbf.index') }}" class="btn btn-sm btn-secondary mt-3">Kembali</a>
        </div>
    </div>
@endsection
