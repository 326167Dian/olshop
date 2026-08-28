@extends('inventory.layouts.app')

@section('header', 'PTO')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Pemantauan Terapi Obat (PTO)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Tanggal 1</th>
                        <th>Tanggal 2</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pto as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->pelanggan->nm_pelanggan ?? $row->nm_pelanggan ?? '-' }}</td>
                            <td>{{ $row->tanggal_1?->format('d-m-Y') }}</td>
                            <td>{{ $row->tanggal_2?->format('d-m-Y') }}</td>
                            <td>{{ $row->created_by }}</td>
                            <td>
                                <a href="{{ route('inventory.pto.show', $row->id_pto) }}" target="_blank"
                                    class="btn btn-info btn-sm">Lihat</a>
                                @if ($isPemilik)
                                    <a href="{{ route('inventory.pto.edit', $row->id_pto) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('inventory.pto.destroy', $row->id_pto) }}" method="POST"
                                        class="d-inline" id="delete-form-{{ $row->id_pto }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDelete('delete-form-{{ $row->id_pto }}', 'data PTO ini')"
                                            class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                                <a href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                    class="btn btn-secondary btn-sm">Riwayat</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
