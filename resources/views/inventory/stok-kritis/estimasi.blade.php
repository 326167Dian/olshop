@extends('inventory.layouts.app')

@section('header', 'Stok Kritis')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ESTIMASI STOK KRITIS</h3>
        </div>
        <div class="card-body table-responsive">
            <span class="btn btn-primary">LAKU</span> Jumlah Transaksi &gt; 10 per 30 hari
            <span class="btn btn-success">LANCAR</span> Jumlah Transaksi 6 - 10 per 30 hari
            <span class="btn btn-warning">SLOW</span> Jumlah Transaksi 1 - 5 per 30 hari
            <hr>

            <a class="btn btn-danger btn-flat mb-2" href="{{ route('inventory.stok-kritis.estimasi.excel') }}" target="_blank">EXPORT TO EXCEL</a>

            <form method="POST" action="{{ route('inventory.stok-kritis.add-to-order') }}" id="frmAddOrder">
                @csrf
                <input type="hidden" name="id_supplier" id="id_supplier_hidden">
                <button class="btn btn-success btn-flat mb-2" type="submit">ADD TO ORDER</button>
                <hr>

                <table id="tabel-estimasi" class="table table-bordered table-striped">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Nama Barang</th>
                            <th>Supplier</th>
                            <th class="text-right">Qty/Stok</th>
                            <th class="text-right">T30</th>
                            <th class="text-center">Q30</th>
                            <th class="text-center">SFC max30</th>
                            <th class="text-center">SFCmax/ week</th>
                            <th class="text-right">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $r)
                            <tr>
                                <td><input type="checkbox" name="kd_barang[]" value="{{ $r->kd_barang }}"> {{ $i + 1 }}</td>
                                <td class="text-center" style="background-color: {{ $r->kategori_color }}; color:#fff;"><strong>{{ $r->kategori_label }}</strong></td>
                                <td>{{ $r->nm_barang }}</td>
                                <td>
                                    <select class="form-control form-control-sm supplier-pilihan">
                                        <option value="">- pilih supplier -</option>
                                        @foreach ($r->supplier_options as $opt)
                                            <option value="{{ $opt->id_supplier }}">{{ $opt->nm_supplier }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-right">{{ $r->stok_barang }}</td>
                                <td class="text-center">{{ $r->t30 }}</td>
                                <td class="text-center">{{ $r->q30 }}</td>
                                <td class="text-center">{{ $r->sfc_max30 }}</td>
                                <td class="text-center">{{ $r->sfc_max_week }}</td>
                                <td class="text-right">{{ $r->sat_barang }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-success" onclick="history.back()">KEMBALI</button>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-estimasi').DataTable({ order: [] });
    });

    @if ($errors->any())
        // Gagal validasi di server (mis. ada barang dengan Konversi 0) --
        // sebelumnya pesan ini hilang begitu saja karena halaman redirect
        // balik ke sini tanpa ada tempat untuk menampilkannya.
        Swal.fire({
            title: 'Gagal menambahkan ke draft pesanan',
            html: '{!! implode('<br>', array_map('e', $errors->all())) !!}',
            icon: 'error',
            confirmButtonText: 'Mengerti'
        });
    @endif

    // Satu surat pesanan hanya untuk satu supplier -- supplier dipilih per baris
    // (dropdown hanya berisi supplier yang pernah tercatat menjual barang itu di
    // barang_supplier). Supplier TIDAK wajib diisi di sini: kalau ada baris
    // tercentang yang belum punya supplier, user ditawari 2 opsi lewat SweetAlert
    // (bukan alert blocking) -- lanjut tanpa supplier (dipilih sendiri nanti di
    // form Pesan Barang) atau perbaiki dulu dropdown yang kosong.
    var frmAddOrder = document.getElementById('frmAddOrder');

    frmAddOrder.addEventListener('submit', function (e) {
        var checked = Array.from(document.querySelectorAll('#frmAddOrder input[name="kd_barang[]"]:checked'));

        if (checked.length === 0) {
            e.preventDefault();
            Swal.fire('Belum ada barang dipilih', 'Pilih minimal satu barang.', 'warning');
            return;
        }

        var supplierIds = new Set();
        var belumPilihSupplier = false;

        checked.forEach(function (cb) {
            var sel = cb.closest('tr').querySelector('.supplier-pilihan');
            if (!sel.value) {
                belumPilihSupplier = true;
            } else {
                supplierIds.add(sel.value);
            }
        });

        if (belumPilihSupplier) {
            e.preventDefault();
            tanyaSupplierBelumLengkap();
            return;
        }

        if (supplierIds.size > 1) {
            e.preventDefault();
            Swal.fire('Supplier berbeda-beda', '1 surat pesanan hanya untuk 1 supplier. Samakan dulu supplier untuk semua barang yang dicentang, atau proses supplier lain secara terpisah.', 'warning');
            return;
        }

        e.preventDefault();
        Swal.fire({
            title: 'Tambahkan item terpilih ke draft pesanan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, tambahkan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                document.getElementById('id_supplier_hidden').value = Array.from(supplierIds)[0];
                frmAddOrder.submit();
            }
        });
    });

    function tanyaSupplierBelumLengkap() {
        Swal.fire({
            title: 'Ada barang yang belum dipilih suppliernya',
            text: 'Lanjutkan tanpa memilih supplier di sini, atau perbaiki dulu input distributornya?',
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Lanjut, pilih distributor sendiri',
            denyButtonText: 'Perbaiki input distributor',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                // Lanjut ke proses selanjutnya tanpa supplier -- dipilih sendiri
                // nanti di form Pesan Barang.
                document.getElementById('id_supplier_hidden').value = '';
                frmAddOrder.submit();
            }
            // isDenied / dibatalkan: biarkan di halaman ini supaya user bisa
            // memperbaiki pilihan supplier pada dropdown yang masih kosong.
        });
    }
</script>
@endpush
