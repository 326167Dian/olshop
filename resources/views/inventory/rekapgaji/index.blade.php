@extends('inventory.layouts.app')

@section('header', 'Rekap Gaji Pegawai')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Rekap Gaji Pegawai</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.rekapgaji.tampil') }}">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="bulan">Bulan</label>
                        <select class="form-control" name="bulan" required>
                            @for ($b = 1; $b <= 12; $b++)
                                <option value="{{ $b }}" {{ $b == (int) date('n') ? 'selected' : '' }}>
                                    {{ \App\Models\GajiDetail::namaBulan($b) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="tahun">Tahun</label>
                        <select class="form-control" name="tahun" required>
                            @for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++)
                                <option value="{{ $t }}" {{ $t == (int) date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-primary">Tampil</button>
                    <a href="{{ route('inventory.gaji.index') }}" class="btn btn-danger">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
