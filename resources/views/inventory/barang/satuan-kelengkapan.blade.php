@extends('inventory.layouts.app')

@section('header', 'Kelengkapan Satuan Bertingkat')

@section('content')
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Kelengkapan Satuan Bertingkat</h3>
            <a href="{{ route('inventory.barang.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Barang
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3>{{ $total }}</h3>
                            <p class="mb-0">Item belum punya satuan bertingkat</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h3 class="text-success">{{ $siapOtomatis }}</h3>
                            <p class="mb-0">Siap dimigrasi otomatis (satuan sudah terkecil: tablet/kapsul/dst)</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $perluManual }}</h3>
                            <p class="mb-0">Perlu dilengkapi manual (satuan masih bisa diperkecil: strip/botol/box/dst)</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($siapOtomatis > 0)
                <form action="{{ route('inventory.barang.satuan-bertingkat.migrate-otomatis') }}" method="POST"
                    onsubmit="return confirm('Migrasi otomatis {{ $siapOtomatis }} item? Ini hanya membuat tingkat satuan dasar dari data harga yang sudah ada.');"
                    class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic"></i> Migrasi Otomatis {{ $siapOtomatis }} Item
                    </button>
                </form>
            @endif

            <p class="text-muted">
                Item yang belum dilengkapi di sini <strong>tetap berjalan normal</strong> pakai skema satuan lama
                (Satuan Retail/Grosir di halaman Barang) — melengkapi di sini bersifat opsional, bertahap.
            </p>

            <table id="tabel-satuan-kelengkapan" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>sat_barang</th>
                        <th>sat_grosir</th>
                        <th>Konversi</th>
                        <th>Kelengkapan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-satuan-kelengkapan').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('inventory.barang.satuan-bertingkat.kelengkapan-data') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kd_barang', name: 'kd_barang' },
                { data: 'nm_barang', name: 'nm_barang' },
                { data: 'sat_barang', name: 'sat_barang' },
                { data: 'sat_grosir', name: 'sat_grosir' },
                { data: 'konversi', name: 'konversi' },
                { data: 'kelengkapan', name: 'kelengkapan', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush
