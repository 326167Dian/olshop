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

    // Satu surat pesanan hanya untuk satu supplier -- supplier dipilih per baris
    // (dropdown hanya berisi supplier yang pernah tercatat menjual barang itu di
    // barang_supplier), tapi semua baris yang dicentang wajib sepakat 1 supplier
    // yang sama sebelum dikirim ke draft Pesan Barang.
    document.getElementById('frmAddOrder').addEventListener('submit', function (e) {
        var checked = Array.from(document.querySelectorAll('#frmAddOrder input[name="kd_barang[]"]:checked'));

        if (checked.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu barang.');
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
            alert('Pilih supplier untuk setiap barang yang dicentang.');
            return;
        }

        if (supplierIds.size > 1) {
            e.preventDefault();
            alert('1 surat pesanan hanya untuk 1 supplier. Samakan dulu supplier untuk semua barang yang dicentang, atau proses supplier lain secara terpisah.');
            return;
        }

        if (!confirm('Tambahkan item terpilih ke draft pesanan?')) {
            e.preventDefault();
            return;
        }

        document.getElementById('id_supplier_hidden').value = Array.from(supplierIds)[0];
    });
</script>
@endpush
