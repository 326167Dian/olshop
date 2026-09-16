@extends('inventory.layouts.app')

@section('header', 'Lembur')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekap Lembur</h3>
        </div>
        <div class="card-body">
            @if ($isPemilik)
                <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.kehadiran.lembur.create') }}">
                    <i class="fas fa-plus"></i> Input Manual
                </a>
            @endif
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <form method="GET" action="{{ route('inventory.kehadiran.lembur.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-0">Tanggal Awal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="{{ $tglAwal }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Tanggal Akhir</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ $tglAkhir }}">
                </div>
                @if ($isPemilik)
                    <div class="col-auto">
                        <label class="form-label mb-0">Pegawai</label>
                        <select name="id_admin" class="form-control">
                            <option value="0">-- Semua Pegawai --</option>
                            @foreach ($pegawaiList as $p)
                                <option value="{{ $p->id_admin }}" {{ $idAdminFilter == $p->id_admin ? 'selected' : '' }}>{{ $p->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Jam Lembur</th>
                            <th>Nominal (Rp)</th>
                            <th>Sumber</th>
                            <th>Status</th>
                            <th>Ditarik ke Gaji</th>
                            @if ($isPemilik)
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lemburList as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->jam_lembur }} jam</td>
                                <td>Rp {{ number_format($row->jam_lembur * ($row->admin->gaji->rate_lembur ?? 0), 0, ',', '.') }}</td>
                                <td>{{ ucfirst($row->sumber) }}</td>
                                <td>
                                    @if ($row->status_approval == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif ($row->status_approval == 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Diajukan</span>
                                    @endif
                                </td>
                                <td>{{ $row->ditarik_ke_gaji ? 'Ya' : 'Belum' }}</td>
                                @if ($isPemilik)
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($row->status_approval == 'diajukan')
                                                <form action="{{ route('inventory.kehadiran.lembur.approve', $row->id_lembur) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                                </form>
                                                <form action="{{ route('inventory.kehadiran.lembur.reject', $row->id_lembur) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                                </form>
                                            @endif
                                            @if (!$row->ditarik_ke_gaji)
                                                <form action="{{ route('inventory.kehadiran.lembur.destroy', $row->id_lembur) }}" method="POST"
                                                    id="delete-form-lembur-{{ $row->id_lembur }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        onclick="confirmDelete('delete-form-lembur-{{ $row->id_lembur }}', '{{ $row->tanggal->format('d-m-Y') }}')"
                                                        class="btn btn-outline-danger btn-sm">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data lembur pada rentang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
