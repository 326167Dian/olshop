@extends('inventory.layouts.app')

@section('header', 'Jenis Pembayaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Jenis Pembayaran Kasir</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.carabayar.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($carabayar as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->nm_carabayar }}</td>
                            <td>
                                <a href="{{ route('inventory.carabayar.edit', $row->id_carabayar) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.carabayar.destroy', $row->id_carabayar) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->id_carabayar }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_carabayar }}', '{{ $row->nm_carabayar }}')"
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
