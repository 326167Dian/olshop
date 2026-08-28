@extends('inventory.layouts.app')

@section('header', 'Supplier')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Supplier</h3>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex flex-wrap gap-1">
                <a class="btn btn-sm btn-primary" href="{{ route('inventory.supplier.create') }}">
                    <i class="fas fa-plus"></i> Tambah
                </a>
                <a class="btn btn-sm btn-danger" href="{{ route('inventory.supplier.print') }}" target="_blank">
                    <i class="fas fa-print"></i> Print Supplier
                </a>
            </div>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>Telp</th>
                        <th>Alamat</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supplier as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->nm_supplier }}</td>
                            <td>{{ $row->tlp_supplier }}</td>
                            <td>{{ $row->alamat_supplier }}</td>
                            <td>{{ $row->ket_supplier }}</td>
                            <td>
                                <a href="{{ route('inventory.supplier.dataobat', $row->id_supplier) }}"
                                    class="btn btn-primary btn-sm">Data Obat</a>
                                <a href="{{ route('inventory.supplier.edit', $row->id_supplier) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <a href="{{ route('inventory.supplier.hutang', $row->id_supplier) }}"
                                    class="btn btn-success btn-sm">Hutang</a>
                                <form action="{{ route('inventory.supplier.destroy', $row->id_supplier) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->id_supplier }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_supplier }}', '{{ $row->nm_supplier }}')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
