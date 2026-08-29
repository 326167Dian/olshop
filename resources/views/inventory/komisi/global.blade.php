@extends('inventory.layouts.app')

@section('header', 'Komisi Global')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Input Komisi Global</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.komisi.index') }}">Kembali</a>

            <form method="POST" action="{{ route('inventory.komisi.global-store') }}" class="row g-2 align-items-end mb-3">
                @csrf
                <div class="col-md-3">
                    <label>Input Nilai Komisi Global (%)</label>
                    <input type="number" min="0" step="1" name="nilai" class="form-control" required
                        value="{{ old('nilai', $persenAktif) }}">
                </div>
                <div class="col-md-3">
                    <label>Status Pemberian Komisi</label>
                    <select name="status" class="form-control">
                        <option value="{{ $statusAktif }}" selected>{{ $statusAktif }}</option>
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>

            <p>Nilai komisi yang aktif saat ini = {{ $persenAktif }} %</p>

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
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-center"><strong>Total</strong></td>
                        <td class="text-end"><strong>Rp {{ number_format($totalKomisi, 0, ',', '.') }},-</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="text-center">
                <a class="btn btn-success" href="{{ route('inventory.komisi.history') }}">History</a>
            </div>
        </div>
    </div>
@endsection
