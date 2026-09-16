@extends('inventory.layouts.app')

@section('header', 'Cuti')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengajuan Cuti</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.kehadiran.cuti.create') }}">
                <i class="fas fa-plus"></i> Ajukan Cuti
            </a>
            @if ($isPemilik)
                <a class="btn btn-sm btn-outline-secondary mb-3" href="{{ route('inventory.kehadiran.cuti.kuota.index') }}">
                    <i class="fas fa-sliders-h"></i> Kelola Kuota Cuti
                </a>
            @endif
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <div class="alert alert-info">Sisa kuota cuti tahunan Anda tahun {{ $tahunIni }}: <b>{{ $sisaKuotaSendiri }} hari</b>.</div>

            <form method="GET" action="{{ route('inventory.kehadiran.cuti.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-0">Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="diajukan" {{ $statusFilter == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="disetujui" {{ $statusFilter == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $statusFilter == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
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
                            <th>Pegawai</th>
                            <th>Jenis</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Jumlah Hari</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            @if ($isPemilik)
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cutiList as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                                <td>{{ ucfirst($row->jenis_cuti) }}</td>
                                <td>{{ $row->tanggal_mulai->format('d-m-Y') }}</td>
                                <td>{{ $row->tanggal_selesai->format('d-m-Y') }}</td>
                                <td>{{ $row->jumlah_hari }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($row->alasan, 40) }}</td>
                                <td>
                                    @if ($row->status == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif ($row->status == 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Diajukan</span>
                                    @endif
                                </td>
                                <td>{{ $row->penyetuju->nama_lengkap ?? '-' }}</td>
                                @if ($isPemilik)
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($row->status == 'diajukan')
                                                <form action="{{ route('inventory.kehadiran.cuti.approve', $row->id_cuti) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                                </form>
                                                <form action="{{ route('inventory.kehadiran.cuti.reject', $row->id_cuti) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('inventory.kehadiran.cuti.destroy', $row->id_cuti) }}" method="POST"
                                                id="delete-form-cuti-{{ $row->id_cuti }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmDelete('delete-form-cuti-{{ $row->id_cuti }}', '{{ $row->admin->nama_lengkap ?? '' }}')"
                                                    class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada pengajuan cuti.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
