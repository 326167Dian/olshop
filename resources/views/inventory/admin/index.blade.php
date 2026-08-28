@extends('inventory.layouts.app')

@section('header', 'Operator')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Operator</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.admin.create') }}">
                <i class="fas fa-plus"></i> Tambah Operator
            </a>
            <a class="btn btn-sm btn-info mb-3" href="{{ route('inventory.admin.login-logs') }}">
                <i class="fas fa-clock"></i> Log Login
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Telp/HP</th>
                        <th>Level</th>
                        <th>Blokir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admins as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->username }}</td>
                            <td>{{ $row->nama_lengkap }}</td>
                            <td>{{ $row->no_telp }}</td>
                            <td>{{ ucfirst($row->akses_level) }}</td>
                            <td>
                                <span class="badge {{ $row->blokir == 'Y' ? 'bg-danger' : 'bg-success' }}">{{ $row->blokir }}</span>
                            </td>
                            <td>
                                <div class="dropdown position-relative d-inline-block">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                                        <a href="{{ route('inventory.admin.edit', $row->id_admin) }}"
                                            class="btn btn-warning btn-sm w-100 mb-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @if ($row->id_admin != 1)
                                            <form action="{{ route('inventory.admin.destroy', $row->id_admin) }}"
                                                method="POST" class="d-inline" id="delete-form-{{ $row->id_admin }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmDelete('delete-form-{{ $row->id_admin }}', '{{ $row->username }}')"
                                                    class="btn btn-danger btn-sm w-100">
                                                    <i class="fa fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
