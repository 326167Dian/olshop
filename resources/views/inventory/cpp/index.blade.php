@extends('inventory.layouts.app')

@section('header', 'CPP')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Catatan Pengobatan Pasien (CPP)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. CPP</th>
                        <th>Nama Pasien</th>
                        <th>Jenis Kelamin</th>
                        <th>Umur</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cpp as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->no_cpp }}</td>
                            <td>{{ $row->nama_pasien }}</td>
                            <td>{{ $row->jk }}</td>
                            <td>{{ $row->umur }}</td>
                            <td>{{ $row->created_at?->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('inventory.cpp.edit', $row->id_cpp) }}"
                                    class="btn btn-info btn-sm">Edit</a>
                                <a href="{{ route('inventory.cpp.show', $row->id_cpp) }}" target="_blank"
                                    class="btn btn-success btn-sm">Cetak</a>
                                <form action="{{ route('inventory.cpp.destroy', $row->id_cpp) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id_cpp }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_cpp }}', 'data CPP ini')"
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
