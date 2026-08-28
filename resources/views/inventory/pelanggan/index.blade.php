@extends('inventory.layouts.app')

@section('header', 'Pelanggan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Pelanggan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.pelanggan.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pelanggan as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->nm_pelanggan }}</td>
                            <td>{{ $row->jenis_kelamin }}</td>
                            <td>{{ $row->tanggal_lahir ? \Carbon\Carbon::parse($row->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $row->tlp_pelanggan }}</td>
                            <td>{{ $row->alamat_pelanggan }}</td>
                            <td>
                                <a href="{{ route('inventory.pelanggan.edit', $row->id_pelanggan) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.pelanggan.destroy', $row->id_pelanggan) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->id_pelanggan }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_pelanggan }}', '{{ $row->nm_pelanggan }}')"
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
