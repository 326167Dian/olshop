@extends('inventory.layouts.app')

@section('header', $order ? 'Ubah Pesanan Barang' : 'Tambah Pesanan Barang')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $order ? 'Ubah Pesanan Barang' : 'Tambah Pesanan Barang' }}</h3>
        </div>
        <div class="card-body">
            <input type="hidden" id="kd_trbmasuk" value="{{ $kdTransaksi }}">
            <input type="hidden" id="id_trbmasuk" value="{{ $order?->id_trbmasuk ?? '' }}">
            <input type="hidden" id="petugas" value="{{ $petugas }}">
            <input type="hidden" id="id_supplier" value="{{ $order?->id_supplier ?? '' }}">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tgl_trbmasuk" id="tgl_trbmasuk" required
                            value="{{ $order?->tgl_trbmasuk?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Kode Transaksi</label>
                        <input type="text" class="form-control" id="kd_hid" value="{{ $kdTransaksi }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nm_supplier" required disabled
                                value="{{ $order?->nm_supplier ?? '' }}">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalSupplier"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" id="tlp_supplier" value="{{ $order?->tlp_supplier ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" id="alamat_supplier" rows="2">{{ $order?->alamat_trbmasuk ?? '' }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Jenis Pesanan</label>
                        <select class="form-control" id="ket_trbmasuk">
                            @foreach (['REGULER', 'PREKURSOR', 'OOT', 'ALKES'] as $jp)
                                <option value="{{ $jp }}" {{ ($order?->ket_trbmasuk ?? 'REGULER') === $jp ? 'selected' : '' }}>{{ $jp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanda Tangan Digital</label>
                        <select class="form-control" id="tandatangan">
                            <option value="TIDAK" {{ ($order?->tandatangan ?? 'TIDAK') === 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                            <option value="YA" {{ ($order?->tandatangan ?? '') === 'YA' ? 'selected' : '' }}>YA</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="simpanTransaksi()">Simpan Transaksi</button>
                        <a href="{{ route('inventory.orders.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <input type="hidden" id="id_barang">
                    <input type="hidden" id="stok_barang">

                    <div class="form-group">
                        <label>Kode Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="kd_barang" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalItem" id="btnBukaModalItem"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group position-relative">
                        <label>Nama Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nmbrg_dtrbmasuk" autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnEnterNama">Enter</button>
                        </div>
                        <div id="panelNamaBarang" class="list-group position-absolute w-100 shadow"
                            style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label>Qty Ecer</label>
                        <input type="number" class="form-control" id="qty_dtrbmasuk" readonly>
                    </div>
                    <div class="form-group">
                        <label>Satuan Ecer</label>
                        <input type="text" class="form-control" id="sat_dtrbmasuk" readonly>
                    </div>
                    <div class="form-group">
                        <label>Konversi</label>
                        <input type="text" class="form-control" id="konversi">
                    </div>
                    <div class="form-group">
                        <label>Qty Grosir</label>
                        <input type="number" class="form-control" id="qtygrosir_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>Satuan Grosir</label>
                        <select class="form-control" id="satgrosir_dtrbmasuk">
                            @foreach ($satuanList as $s)
                                <option value="{{ $s->nm_satuan }}">{{ $s->nm_satuan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Beli</label>
                        <input type="text" class="form-control" id="hrgsat_dtrbmasuk">
                    </div>
                    <button type="button" class="btn btn-success mt-2" onclick="simpanDetail()">Simpan Detail</button>
                </div>
            </div>

            <hr>
            <div id="tabeldata"></div>
        </div>
    </div>

    <!-- Modal Supplier -->
    <div class="modal fade" id="modalSupplier" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Supplier</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="daftarSupplierBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Item (per supplier, T30/Q30/SF) -->
    <div class="modal fade" id="modalItem" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Item Barang (Status Traffic Order)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <p class="text-muted small">T30 = jumlah transaksi 30 hari terakhir, Q30 = total qty terjual 30 hari
                        terakhir, SF = Q30 - stok saat ini (perkiraan kebutuhan restock).</p>
                    <table id="tabel-item-supplier" class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>T30</th>
                                <th>Q30</th>
                                <th>SF</th>
                                <th>Harga Beli</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var itemSupplierTable = null;

    function loadTabelDetail() {
        var kd = document.getElementById('kd_trbmasuk').value;
        fetch("{{ route('inventory.orders.detail.index') }}?kd_trbmasuk=" + encodeURIComponent(kd), { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) { $('#tabeldata').html(html); });
    }

    document.addEventListener('DOMContentLoaded', loadTabelDetail);

    // --- Modal Supplier ---
    document.getElementById('modalSupplier').addEventListener('show.bs.modal', function() {
        fetch("{{ route('inventory.supplier.index') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    });

    var supplierData = @json($supplierList);
    (function renderSupplierList() {
        var body = document.getElementById('daftarSupplierBody');
        supplierData.forEach(function(s, idx) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (idx + 1) + '</td><td>' + s.nm_supplier + '</td><td>' + (s.tlp_supplier || '') +
                '</td><td>' + (s.alamat_supplier || '') + '</td><td><button type="button" class="btn btn-xs btn-info btn-pilih-supplier">Pilih</button></td>';
            tr.querySelector('.btn-pilih-supplier').addEventListener('click', function() {
                document.getElementById('id_supplier').value = s.id_supplier;
                document.getElementById('nm_supplier').value = s.nm_supplier;
                document.getElementById('tlp_supplier').value = s.tlp_supplier || '';
                document.getElementById('alamat_supplier').value = s.alamat_supplier || '';
                bootstrap.Modal.getInstance(document.getElementById('modalSupplier')).hide();
            });
            body.appendChild(tr);
        });
    })();

    // --- Modal Item per supplier ---
    document.getElementById('modalItem').addEventListener('show.bs.modal', function() {
        var idSupplier = document.getElementById('id_supplier').value;
        if (!idSupplier) {
            alert('Pilih supplier terlebih dahulu');
            bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
            return;
        }

        if (itemSupplierTable) {
            itemSupplierTable.destroy();
            $('#tabel-item-supplier').empty();
        }

        itemSupplierTable = $('#tabel-item-supplier').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventory.orders.supplier-items') }}?id_supplier=" + idSupplier,
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-center' },
                { data: 'satuan', className: 'text-center' },
                { data: 't30', className: 'text-center' },
                { data: 'q30', className: 'text-center' },
                { data: 'sf', className: 'text-center' },
                { data: 'harga_beli', className: 'text-end' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });

    $(document).on('click', '.btn-pilih-barang', function() {
        resolveItemByName($(this).data('nm_barang'));
        bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
    });

    // --- Resolve item by name/code, autofill form ---
    function fillItemForm(d) {
        document.getElementById('id_barang').value = d.id_barang;
        document.getElementById('kd_barang').value = d.kd_barang;
        document.getElementById('nmbrg_dtrbmasuk').value = d.nm_barang;
        document.getElementById('stok_barang').value = d.stok_barang;
        document.getElementById('qty_dtrbmasuk').value = d.konversi;
        document.getElementById('sat_dtrbmasuk').value = d.sat_barang;
        document.getElementById('qtygrosir_dtrbmasuk').value = '1';
        document.getElementById('satgrosir_dtrbmasuk').value = d.sat_grosir;
        document.getElementById('konversi').value = d.konversi;
        document.getElementById('hrgsat_dtrbmasuk').value = d.hrgsat_barang;
    }

    function resolveItemByName(nama) {
        var form = new FormData();
        form.append('nm_barang', nama);
        fetch("{{ route('inventory.orders.item-resolve') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function(d) {
                if (d.id_barang) fillItemForm(d);
                else alert(d.message || 'Barang tidak ditemukan');
            });
    }

    function resolveItemByCode(kode) {
        var form = new FormData();
        form.append('kd_barang', kode);
        fetch("{{ route('inventory.orders.item-resolve') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function(d) {
                if (d.id_barang) fillItemForm(d);
                else alert(d.message || 'Barang tidak ditemukan');
            });
    }

    document.getElementById('kd_barang').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            resolveItemByCode(this.value);
        }
    });

    document.getElementById('btnEnterNama').addEventListener('click', function() {
        resolveItemByName(document.getElementById('nmbrg_dtrbmasuk').value);
    });
    document.getElementById('nmbrg_dtrbmasuk').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            resolveItemByName(this.value);
        }
    });

    // --- Typeahead nama barang ---
    var searchDelay = null;
    document.getElementById('nmbrg_dtrbmasuk').addEventListener('input', function() {
        var input = this;
        var panel = document.getElementById('panelNamaBarang');
        var keyword = input.value.trim();
        clearTimeout(searchDelay);
        if (keyword.length < 2) {
            panel.style.display = 'none';
            return;
        }
        searchDelay = setTimeout(function() {
            var form = new FormData();
            form.append('query', keyword);
            fetch("{{ route('inventory.orders.item-search') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    panel.innerHTML = '';
                    if (!data || data.length === 0) {
                        panel.style.display = 'none';
                        return;
                    }
                    data.forEach(function(item) {
                        var div = document.createElement('div');
                        div.className = 'list-group-item list-group-item-action';
                        div.style.cursor = 'pointer';
                        div.textContent = item.nm_barang + ' (Stok: ' + item.stok_barang + ' ' + item.sat_barang + ')';
                        div.addEventListener('click', function() {
                            document.getElementById('nmbrg_dtrbmasuk').value = item.nm_barang;
                            panel.style.display = 'none';
                            resolveItemByName(item.nm_barang);
                        });
                        panel.appendChild(div);
                    });
                    panel.style.display = 'block';
                });
        }, 300);
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#nmbrg_dtrbmasuk') && !e.target.closest('#panelNamaBarang')) {
            document.getElementById('panelNamaBarang').style.display = 'none';
        }
    });

    // --- Qty <-> Qty Grosir <-> Konversi sinkron ---
    document.getElementById('qty_dtrbmasuk').addEventListener('input', hitungGrosirDariEcer);
    document.getElementById('qtygrosir_dtrbmasuk').addEventListener('input', hitungEcerDariGrosir);
    document.getElementById('konversi').addEventListener('input', hitungEcerDariGrosir);

    function hitungGrosirDariEcer() {
        var qty = parseFloat(document.getElementById('qty_dtrbmasuk').value) || 0;
        var konversi = parseFloat(document.getElementById('konversi').value) || 1;
        document.getElementById('qtygrosir_dtrbmasuk').value = konversi ? (qty / konversi) : 0;
    }

    function hitungEcerDariGrosir() {
        var qtyGrosir = parseFloat(document.getElementById('qtygrosir_dtrbmasuk').value) || 0;
        var konversi = parseFloat(document.getElementById('konversi').value) || 0;
        document.getElementById('qty_dtrbmasuk').value = qtyGrosir * konversi;
    }

    // --- Simpan Detail ---
    function simpanDetail() {
        var nmbrg = document.getElementById('nmbrg_dtrbmasuk').value;
        var qty = document.getElementById('qty_dtrbmasuk').value;
        var harga = document.getElementById('hrgsat_dtrbmasuk').value;
        var konversi = document.getElementById('konversi').value;

        if (nmbrg === '') { alert('Belum ada Item terpilih'); return; }
        if (qty === '') { alert('Qty tidak boleh kosong'); return; }
        if (harga === '') { alert('Harga tidak boleh kosong'); return; }
        if (konversi === '0' || konversi === '') { alert('Konversi tidak boleh kosong'); return; }

        var form = new FormData();
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('id_barang', document.getElementById('id_barang').value);
        form.append('kd_barang', document.getElementById('kd_barang').value);
        form.append('nmbrg_dtrbmasuk', nmbrg);
        form.append('qty_dtrbmasuk', qty);
        form.append('sat_dtrbmasuk', document.getElementById('sat_dtrbmasuk').value);
        form.append('hrgsat_dtrbmasuk', harga);
        form.append('qtygrosir_dtrbmasuk', document.getElementById('qtygrosir_dtrbmasuk').value);
        form.append('satgrosir_dtrbmasuk', document.getElementById('satgrosir_dtrbmasuk').value);
        form.append('konversi', konversi);

        fetch("{{ route('inventory.orders.detail.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function() {
                ['id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk', 'sat_dtrbmasuk',
                    'hrgsat_dtrbmasuk', 'qtygrosir_dtrbmasuk', 'konversi'
                ].forEach(function(id) { document.getElementById(id).value = ''; });
                loadTabelDetail();
            });
    }

    // --- Simpan Transaksi (finalisasi header) ---
    function simpanTransaksi() {
        var nmSupplier = document.getElementById('nm_supplier').value;
        if (nmSupplier === '') { alert('Belum ada data supplier'); return; }

        var ttl = (document.getElementById('ttl_trkasir') ? document.getElementById('ttl_trkasir').value : '0').split('.').join('');
        var dp = (document.getElementById('dp_bayar') ? document.getElementById('dp_bayar').value : '0').split('.').join('');
        var sisa = (document.getElementById('sisa_bayar') ? document.getElementById('sisa_bayar').value : '0').split('.').join('');

        var isEdit = document.getElementById('id_trbmasuk').value !== '';
        var url = isEdit ?
            "{{ $order ? route('inventory.orders.update', $order->id_trbmasuk) : '' }}" :
            "{{ route('inventory.orders.store') }}";

        var form = new FormData();
        form.append('_token', '{{ csrf_token() }}');
        if (isEdit) form.append('_method', 'PUT');
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('tgl_trbmasuk', document.getElementById('tgl_trbmasuk').value);
        form.append('id_supplier', document.getElementById('id_supplier').value);
        form.append('nm_supplier', nmSupplier);
        form.append('tlp_supplier', document.getElementById('tlp_supplier').value);
        form.append('alamat_trbmasuk', document.getElementById('alamat_supplier').value);
        form.append('ket_trbmasuk', document.getElementById('ket_trbmasuk').value);
        form.append('ttl_trbmasuk', ttl || '0');
        form.append('dp_bayar', dp || '0');
        form.append('sisa_bayar', sisa || '0');
        form.append('tandatangan', document.getElementById('tandatangan').value);

        fetch(url, { method: 'POST', body: form })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menyimpan transaksi');
                window.location = "{{ route('inventory.orders.index') }}";
            })
            .catch(function() { alert('Proses gagal, periksa kembali data yang diisi.'); });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F3') { event.preventDefault(); simpanTransaksi(); }
    });
</script>
@endpush
