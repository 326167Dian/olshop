@extends('inventory.layouts.app')

@section('header', 'Konseling')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Konseling</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.konseling.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Pelanggan</th>
                        <th>Dokter</th>
                        <th>Diagnosa</th>
                        <th>Tindakan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($konseling as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_konseling)->format('d-m-Y') }}</td>
                            <td>{{ $row->nm_pelanggan }}</td>
                            <td>{{ $row->nama_dokter }}</td>
                            <td>{{ $row->diagnosa }}</td>
                            <td>{{ $row->tindakan }}</td>
                            <td>
                                <a href="{{ route('inventory.konseling.print', $row->id_konseling) }}" target="_blank"
                                    class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                <a href="{{ route('inventory.konseling.edit', $row->id_konseling) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('inventory.konseling.destroy', $row->id_konseling) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->id_konseling }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_konseling }}', 'data konseling ini')"
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
