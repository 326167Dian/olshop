@extends('inventory.layouts.app')

@section('header', 'Slip Gaji Karyawan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Slip Gaji Karyawan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.gajidetail.create') }}">
                <i class="fas fa-plus"></i> Tambah
            </a>
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.gaji.index') }}">
                Kembali ke Data Gaji
            </a>

            <form method="GET" action="{{ route('inventory.gajidetail.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label mb-0">Periode</label>
                    <select name="bulan" class="form-control">
                        <option value="0">-- Semua Bulan --</option>
                        @for ($b = 1; $b <= 12; $b++)
                            <option value="{{ $b }}" {{ $b == $bulanFilter ? 'selected' : '' }}>
                                {{ \App\Models\GajiDetail::namaBulan($b) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">&nbsp;</label>
                    <select name="tahun" class="form-control">
                        <option value="0">-- Semua Tahun --</option>
                        @for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++)
                            <option value="{{ $t }}" {{ $t == $tahunFilter ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('inventory.gajidetail.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Nama Karyawan</th>
                            <th>Jumlah Hari</th>
                            <th>Gaji Pokok</th>
                            <th>Transportasi / Extra fooding</th>
                            <th>Lembur</th>
                            <th>Komisi</th>
                            <th>Pinjaman / Kasbon</th>
                            <th>Total</th>
                            <th>Dibuat Oleh</th>
                            <th>Disetujui Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($slipList as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->periode_text }}</td>
                                <td>{{ $row->admin->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->jumlah_hari }}</td>
                                <td>Rp {{ number_format($row->gaji_pokok, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->transportasi, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->lembur, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->komisi, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->pinjaman, 0, ',', '.') }}</td>
                                <td><b>Rp {{ number_format($row->total, 0, ',', '.') }}</b></td>
                                <td>{{ $row->pembuat->nama_lengkap ?? '-' }}</td>
                                <td>
                                    @if ($row->penyetuju)
                                        {{ $row->penyetuju->nama_lengkap }}
                                    @else
                                        <span class="badge bg-secondary">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ route('inventory.gajidetail.cetak', $row->id_gaji_detail) }}"
                                            target="_blank" title="Cetak Slip" class="btn btn-info btn-sm">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                        <a href="{{ route('inventory.gajidetail.edit', $row->id_gaji_detail) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('inventory.gajidetail.destroy', $row->id_gaji_detail) }}"
                                            method="POST" class="d-inline"
                                            id="delete-form-gajidetail-{{ $row->id_gaji_detail }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-gajidetail-{{ $row->id_gaji_detail }}', '{{ $row->periode_text }} - {{ $row->admin->nama_lengkap ?? '' }}')"
                                                class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
