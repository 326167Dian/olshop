@extends('inventory.layouts.app')

@section('header', 'Buka Kasir')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Buka Kasir</h3>
        </div>
        <div class="card-body">
            @if ($sudahOn)
                <div class="alert alert-warning">Kasir sudah dibuka hari ini.</div>
                <a href="{{ route('inventory.shiftkerja.index') }}" class="btn btn-secondary">Kembali</a>
            @else
                <form method="POST" action="{{ route('inventory.shiftkerja.buka.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-control" required>
                            @foreach ($shiftList as $s)
                                <option value="{{ $s->shift }}">SHIFT {{ strtoupper($s->nama_shift) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Saldo Awal</label>
                        <input type="number" step="any" name="saldoawal" class="form-control" required autofocus>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('inventory.shiftkerja.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
