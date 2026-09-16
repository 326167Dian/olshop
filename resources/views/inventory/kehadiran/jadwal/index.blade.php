@extends('inventory.layouts.app')

@section('header', 'Jadwal Shift')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Jadwal Shift</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.kehadiran.jadwal.create') }}">
                <i class="fas fa-plus"></i> {{ $isPemilik ? 'Jadwalkan Pegawai' : 'Ajukan Jadwal' }}
            </a>
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <form method="GET" action="{{ route('inventory.kehadiran.jadwal.index') }}" class="row g-2 align-items-end mb-3">
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
                            <th>Shift</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwalList as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->shift->nama_shift ?? '-' }} ({{ $row->shift->jam_masuk ?? '-' }}-{{ $row->shift->jam_pulang ?? '-' }})</td>
                                <td>
                                    @if ($row->status_approval == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif ($row->status_approval == 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Diajukan</span>
                                    @endif
                                </td>
                                <td>{{ $row->penyetuju->nama_lengkap ?? '-' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($isPemilik && $row->status_approval == 'diajukan')
                                            <form action="{{ route('inventory.kehadiran.jadwal.approve', $row->id_jadwal) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                            </form>
                                            <form action="{{ route('inventory.kehadiran.jadwal.reject', $row->id_jadwal) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('inventory.kehadiran.jadwal.destroy', $row->id_jadwal) }}" method="POST"
                                            id="delete-form-jadwal-{{ $row->id_jadwal }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-jadwal-{{ $row->id_jadwal }}', '{{ $row->tanggal->format('d-m-Y') }}')"
                                                class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada jadwal pada rentang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
