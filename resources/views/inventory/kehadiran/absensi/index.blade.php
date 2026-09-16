@extends('inventory.layouts.app')

@section('header', 'Absensi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekap Absensi</h3>
        </div>
        <div class="card-body">
            @if ($isPemilik)
                <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.kehadiran.absensi.create') }}">
                    <i class="fas fa-plus"></i> Input Manual
                </a>
                <form action="{{ route('inventory.kehadiran.absensi.generate-alpha') }}" method="POST" class="d-inline mb-3">
                    @csrf
                    <input type="hidden" name="tgl_awal" value="{{ $tglAwal }}">
                    <input type="hidden" name="tgl_akhir" value="{{ \Illuminate\Support\Carbon::parse($tglAkhir)->isPast() ? $tglAkhir : now()->subDay()->toDateString() }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-user-times"></i> Tandai Alpha Otomatis
                    </button>
                </form>
            @endif
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <form method="GET" action="{{ route('inventory.kehadiran.absensi.index') }}" class="row g-2 align-items-end mb-3">
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

            <div class="mb-3">
                @foreach (['hadir' => 'success', 'terlambat' => 'warning', 'alpha' => 'danger', 'izin' => 'info', 'cuti' => 'secondary'] as $status => $warna)
                    <span class="badge bg-{{ $warna }}">{{ ucfirst($status) }}: {{ $rekapStatus[$status] ?? 0 }}</span>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pegawai</th>
                            <th>Shift</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Sumber</th>
                            @if ($isPemilik)
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absensiList as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->shift->nama_shift ?? '-' }}</td>
                                <td>{{ $row->jam_masuk ?? '-' }}</td>
                                <td>{{ $row->jam_pulang ?? '-' }}</td>
                                <td>
                                    @php
                                        $warna = ['hadir' => 'success', 'terlambat' => 'warning', 'alpha' => 'danger', 'izin' => 'info', 'cuti' => 'secondary'][$row->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $warna }}">{{ ucfirst($row->status) }}</span>
                                </td>
                                <td>{{ $row->sumber }}</td>
                                @if ($isPemilik)
                                    <td>
                                        <a href="{{ route('inventory.kehadiran.absensi.edit', $row->id_absensi) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('inventory.kehadiran.absensi.destroy', $row->id_absensi) }}" method="POST" class="d-inline"
                                            id="delete-form-absensi-{{ $row->id_absensi }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-absensi-{{ $row->id_absensi }}', '{{ $row->tanggal->format('d-m-Y') }}')"
                                                class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data absensi pada rentang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
