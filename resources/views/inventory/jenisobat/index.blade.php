@extends('inventory.layouts.app')

@section('header', 'Jenis Obat & Rak Obat')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Jenis Obat dan Rak Obat</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.jenisobat.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis & Rak Obat</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jenisobat as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->jenisobat }}</td>
                            <td>{{ $row->ket }}</td>
                            <td>
                                <a href="{{ route('inventory.jenisobat.edit', $row->idjenis) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.jenisobat.destroy', $row->idjenis) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->idjenis }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->idjenis }}', '{{ $row->jenisobat }}')"
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
