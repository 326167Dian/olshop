@extends('inventory.layouts.app')

@section('header', 'Kuota Cuti')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kelola Kuota Cuti Tahunan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.cuti.index') }}">Kembali</a>

            <form method="GET" action="{{ route('inventory.kehadiran.cuti.kuota.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-0">Tahun</label>
                    <select name="tahun" class="form-control">
                        @for ($t = (int) date('Y') - 1; $t <= (int) date('Y') + 1; $t++)
                            <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Tampil</button>
                </div>
            </form>

            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Kuota (hari)</th>
                        <th>Terpakai (hari)</th>
                        <th>Sisa (hari)</th>
                        <th>Ubah Kuota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pegawaiList as $p)
                        <tr>
                            <td>{{ $p->nama_lengkap }}</td>
                            <td>{{ $p->kuota_hari }}</td>
                            <td>{{ $p->terpakai }}</td>
                            <td>{{ $p->sisa }}</td>
                            <td>
                                <form method="POST" action="{{ route('inventory.kehadiran.cuti.kuota.update') }}" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id_admin" value="{{ $p->id_admin }}">
                                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                                    <input type="number" name="kuota_hari" min="0" value="{{ $p->kuota_hari }}" class="form-control form-control-sm" style="width:80px">
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
