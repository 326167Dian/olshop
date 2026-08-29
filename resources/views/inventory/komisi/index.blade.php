@extends('inventory.layouts.app')

@section('header', 'Komisi Pegawai')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah dan Tutup Komisi</h3>
        </div>
        <div class="card-body">
            @if ($isPemilik)
                <div class="mb-3 d-flex flex-wrap gap-1">
                    <a class="btn btn-sm btn-success" href="{{ route('inventory.komisi.massal') }}">Atur Komisi</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('inventory.komisi.global') }}">Komisi Global</a>
                    <form action="{{ route('inventory.komisi.destroy-all') }}" method="POST" id="hapus-semua-komisi">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-warning"
                            onclick="confirmDelete('hapus-semua-komisi', 'SEMUA komisi item')">Hapus Semua Komisi</button>
                    </form>
                </div>
            @endif

            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th style="text-align:right;">Qty/Stok</th>
                        <th style="text-align:right;">Satuan</th>
                        <th style="text-align:center;">Jenis Obat</th>
                        <th style="text-align:right;">Harga Jual</th>
                        <th style="text-align:right;">Komisi Pegawai</th>
                        @if ($isPemilik)
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($komisi as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->kd_barang }}</td>
                            <td>{{ $row->nm_barang }}</td>
                            <td class="text-center">{{ $row->stok_barang }}</td>
                            <td class="text-center">{{ $row->sat_barang }}</td>
                            <td class="text-center">{{ $row->jenisobat }}</td>
                            <td class="text-end">{{ number_format($row->hrgjual_barang, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->komisi, 0, ',', '.') }}</td>
                            @if ($isPemilik)
                                <td class="text-center" style="width:80px;">
                                    <a href="{{ route('inventory.komisi.edit', $row->id_barang) }}" title="EDIT"
                                        class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('inventory.komisi.destroy', $row->id_barang) }}" method="POST"
                                        class="d-inline" id="delete-komisi-{{ $row->id_barang }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDelete('delete-komisi-{{ $row->id_barang }}', 'komisi {{ $row->nm_barang }}')"
                                            class="btn btn-danger btn-xs">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
