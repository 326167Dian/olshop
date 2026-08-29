@extends('inventory.layouts.app')

@section('header', 'History Komisi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Komisi Bulan {{ $namaBulan }} {{ $tahun }}</h3>
        </div>
        <div class="card-body">
            <table class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Admin</th>
                        <th style="text-align:center;">Bulan</th>
                        <th style="text-align:right;">Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['nama_lengkap'] }}</td>
                            <td class="text-center">{{ $namaBulan }}</td>
                            <td class="text-end">Rp {{ number_format($row['komisi'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data petugas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <a href="{{ route('inventory.komisi.history') }}" class="btn btn-danger">Kembali</a>
        </div>
    </div>
@endsection
