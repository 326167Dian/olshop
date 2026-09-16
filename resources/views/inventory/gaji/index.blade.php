@extends('inventory.layouts.app')

@section('header', 'Gaji Karyawan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Gaji Karyawan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.gaji.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <a class="btn btn-sm btn-info mb-3" href="{{ route('inventory.rekapgaji.index') }}">
                <i class="fas fa-file-invoice-dollar"></i> Rekap Gaji Pegawai
            </a>

            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Karyawan</th>
                        <th>Gaji Pokok</th>
                        <th>Transportasi/Ekstra Fooding</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gajiList as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->admin->username ?? '-' }}</td>
                            <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                            <td>Rp {{ number_format($row->gaji_harian, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row->transportasi_harian, 0, ',', '.') }}</td>
                            <td>
                                @if ($row->status_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.gajidetail.create', ['id_gaji' => $row->id_gaji]) }}"
                                    title="Tambah Slip Gaji" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-invoice"></i> Slip
                                </a>
                                <a href="{{ route('inventory.gaji.edit', $row->id_gaji) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.gaji.destroy', $row->id_gaji) }}" method="POST"
                                    class="d-inline" id="delete-form-gaji-{{ $row->id_gaji }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-gaji-{{ $row->id_gaji }}', '{{ $row->admin->nama_lengkap ?? '' }}')"
                                        class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
