@extends('inventory.layouts.app')

@section('header', 'Catatan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT CATATAN</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.catatan.update', $catatan) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="deskripsi" class="form-control" rows="6" required>{{ old('deskripsi', strip_tags(str_replace('<br />', "\n", $catatan->deskripsi))) }}</textarea>
                    @error('deskripsi')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">SIMPAN</button>
                <button type="button" class="btn btn-danger" onclick="history.back()">BATAL</button>
            </form>
        </div>
    </div>
@endsection
