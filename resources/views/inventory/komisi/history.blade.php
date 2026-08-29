@extends('inventory.layouts.app')

@section('header', 'History Komisi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Input Bulan</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.komisi.history-result') }}">
                @csrf
                <div class="form-group" style="max-width:250px;">
                    <label>Bulan</label>
                    <select name="bulan" class="form-control" required>
                        @foreach ($bulanIndo as $num => $nama)
                            <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('inventory.komisi.global') }}" class="btn btn-danger">Kembali</a>
            </form>
        </div>
    </div>
@endsection
