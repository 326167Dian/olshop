@extends('inventory.layouts.app')

@section('header', 'Catatan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TAMBAH CATATAN</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.catatan.store') }}">
                @csrf
                <input type="hidden" name="petugas" value="{{ $petugas }}">
                <input type="hidden" name="shift" value="{{ $shift }}">

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Tanggal</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" name="tgl" value="{{ $tglHariIni }}">
                    </div>
                </div>

                @if (is_null($shift))
                    <div class="alert alert-warning">Belum ada shift kasir yang dibuka hari ini — catatan akan tersimpan tanpa shift yang valid.</div>
                @endif

                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="deskripsi" class="form-control" rows="6" required>{{ old('deskripsi') }}</textarea>
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
