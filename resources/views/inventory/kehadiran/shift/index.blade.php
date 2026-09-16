@extends('inventory.layouts.app')

@section('header', 'Master Shift')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Master Shift</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.kehadiran.shift.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Shift</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Toleransi Telat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shiftList as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->nama_shift }}</td>
                            <td>{{ $row->jam_masuk }}</td>
                            <td>{{ $row->jam_pulang }}</td>
                            <td>{{ $row->toleransi_telat }} menit</td>
                            <td>
                                @if ($row->status_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.kehadiran.shift.edit', $row->id_shift) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.kehadiran.shift.destroy', $row->id_shift) }}" method="POST"
                                    class="d-inline" id="delete-form-shift-{{ $row->id_shift }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-shift-{{ $row->id_shift }}', '{{ $row->nama_shift }}')"
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
