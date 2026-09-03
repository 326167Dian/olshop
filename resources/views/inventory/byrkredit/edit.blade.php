@extends('inventory.layouts.app')

@section('header', 'Edit/Retur/Hapus Pembelian')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Transaksi Barang Masuk — {{ $trbmasuk->kd_trbmasuk }}</h3>
        </div>
        <div class="card-body">
            <input type="hidden" id="id_trbmasuk" value="{{ $trbmasuk->id_trbmasuk }}">
            <input type="hidden" id="kd_trbmasuk" value="{{ $trbmasuk->kd_trbmasuk }}">
            <input type="hidden" id="petugas" value="{{ Auth::guard('admin')->user()->nama_lengkap }}">
            <input type="hidden" id="id_supplier" value="{{ $trbmasuk->id_supplier }}">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="tgl_trbmasuk" required value="{{ $trbmasuk->tgl_trbmasuk?->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $trbmasuk->kd_trbmasuk }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nm_supplier" required disabled value="{{ $trbmasuk->nm_supplier }}">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalSupplier"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" id="tlp_supplier" value="{{ $trbmasuk->tlp_supplier }}">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" id="alamat_trbmasuk" rows="2">{{ $trbmasuk->alamat_trbmasuk }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Keterangan / No Faktur</label>
                        <input type="text" class="form-control" id="ket_trbmasuk" value="{{ $trbmasuk->ket_trbmasuk }}">
                    </div>
                    <div class="form-group" id="groupJatuhTempo">
                        <label>Jatuh Tempo</label>
                        <input type="date" class="form-control" id="jatuhtempo" value="{{ $trbmasuk->jatuhtempo }}">
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="simpanTransaksi()">Simpan Transaksi</button>
                        <a href="{{ route('inventory.byrkredit.index') }}" class="btn btn-secondary">Batal</a>
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
                                data-bs-target="#modalItem"><i class="fa fa-search"></i></button>
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
                        <label>Qty</label>
                        <input type="number" step="any" class="form-control" id="qty_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <input type="text" class="form-control" id="sat_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>No. Batch</label>
                        <input type="text" class="form-control" id="no_batch">
                    </div>
                    <div class="form-group">
                        <label>Tgl. Kadaluarsa</label>
                        <input type="date" class="form-control" id="exp_date" value="{{ now()->addDays(720)->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Harga Beli</label>
                        <input type="text" class="form-control" id="hrgsat_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual Umum</label>
                        <input type="text" class="form-control" id="hrgjual_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual Resep</label>
                        <input type="text" class="form-control" id="hrgjual_dtrbmasuk_resep">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual Nakes</label>
                        <input type="text" class="form-control" id="hrgjual_dtrbmasuk_nakes">
                    </div>
                    <button type="button" class="btn btn-success mt-2" onclick="simpanDetail()">Simpan Detail</button>
                </div>
            </div>

            <hr>
            <p class="text-muted">Untuk retur/pengembalian barang ke supplier: kurangi Qty langsung di tabel, atau hapus
                baris item sepenuhnya kalau seluruh item pada baris tersebut dikembalikan.</p>
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
                    <table id="tabel-pilih-supplier" class="table table-sm table-bordered table-striped w-100">
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

    <!-- Modal Item (pemilihan barang global, tidak dibatasi supplier) -->
    <div class="modal fade" id="modalItem" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Item Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table id="tabel-item-picker" class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
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
    var itemPickerTable = null;

    function loadTabelDetail() {
        var kd = document.getElementById('kd_trbmasuk').value;
        fetch("{{ route('inventory.byrkredit.detail.index') }}?kd_trbmasuk=" + encodeURIComponent(kd) + "&mode=byrkredit", { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) { $('#tabeldata').html(html); });
    }

    document.addEventListener('DOMContentLoaded', loadTabelDetail);

    // --- Modal Supplier ---
    var supplierData = @json($supplierList ?? []);
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
                document.getElementById('alamat_trbmasuk').value = s.alamat_supplier || '';
                bootstrap.Modal.getInstance(document.getElementById('modalSupplier')).hide();
            });
            body.appendChild(tr);
        });
        $('#tabel-pilih-supplier').DataTable({
            responsive: true,
            autoWidth: false,
            columnDefs: [{ orderable: false, searchable: false, targets: -1 }],
        });
    })();

    // --- Modal Item global ---
    document.getElementById('modalItem').addEventListener('show.bs.modal', function() {
        if (itemPickerTable) {
            itemPickerTable.destroy();
            $('#tabel-item-picker').empty();
        }
        itemPickerTable = $('#tabel-item-picker').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventory.byrkredit.item-picker') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-center' },
                { data: 'sat_barang', className: 'text-center' },
                { data: 'hrgsat_barang', className: 'text-end' },
                { data: 'hrgjual_barang', className: 'text-end' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });

    $(document).on('click', '.btn-pilih-barang', function() {
        resolveItemByName($(this).data('nm_barang'));
        bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
    });

    function fillItemForm(d) {
        document.getElementById('id_barang').value = d.id_barang;
        document.getElementById('kd_barang').value = d.kd_barang;
        document.getElementById('nmbrg_dtrbmasuk').value = d.nm_barang;
        document.getElementById('stok_barang').value = d.stok_barang;
        document.getElementById('sat_dtrbmasuk').value = d.sat_barang;
        document.getElementById('hrgsat_dtrbmasuk').value = d.hrgsat_barang;
        document.getElementById('hrgjual_dtrbmasuk').value = d.hrgjual_barang;
        document.getElementById('hrgjual_dtrbmasuk_resep').value = d.hrgjual_barang1;
        document.getElementById('hrgjual_dtrbmasuk_nakes').value = d.hrgjual_barang2;
    }

    function resolveItemByName(nama) {
        var form = new FormData();
        form.append('nm_barang', nama);
        fetch("{{ route('inventory.byrkredit.item-resolve') }}", {
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
        fetch("{{ route('inventory.byrkredit.item-resolve') }}", {
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
        if (e.key === 'Enter') { e.preventDefault(); resolveItemByCode(this.value); }
    });

    document.getElementById('btnEnterNama').addEventListener('click', function() {
        resolveItemByName(document.getElementById('nmbrg_dtrbmasuk').value);
    });
    document.getElementById('nmbrg_dtrbmasuk').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); resolveItemByName(this.value); }
    });

    // --- Typeahead nama barang ---
    var searchDelay = null;
    document.getElementById('nmbrg_dtrbmasuk').addEventListener('input', function() {
        var input = this;
        var panel = document.getElementById('panelNamaBarang');
        var keyword = input.value.trim();
        clearTimeout(searchDelay);
        if (keyword.length < 2) { panel.style.display = 'none'; return; }
        searchDelay = setTimeout(function() {
            var form = new FormData();
            form.append('query', keyword);
            fetch("{{ route('inventory.byrkredit.item-search') }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    panel.innerHTML = '';
                    if (!data || data.length === 0) { panel.style.display = 'none'; return; }
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

    // --- Simpan Detail ---
    function simpanDetail() {
        var nmbrg = document.getElementById('nmbrg_dtrbmasuk').value;
        var qty = document.getElementById('qty_dtrbmasuk').value;
        var harga = document.getElementById('hrgsat_dtrbmasuk').value;
        var noBatch = document.getElementById('no_batch').value;

        if (nmbrg === '') { alert('Belum ada Item terpilih'); return; }
        if (qty === '') { alert('Qty tidak boleh kosong'); return; }
        if (harga === '') { alert('Harga tidak boleh kosong'); return; }
        if (noBatch === '') { alert('No. Batch tidak boleh kosong'); return; }

        var form = new FormData();
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('id_barang', document.getElementById('id_barang').value);
        form.append('kd_barang', document.getElementById('kd_barang').value);
        form.append('nmbrg_dtrbmasuk', nmbrg);
        form.append('qty_dtrbmasuk', qty);
        form.append('sat_dtrbmasuk', document.getElementById('sat_dtrbmasuk').value);
        form.append('hrgsat_dtrbmasuk', harga);
        form.append('hrgjual_dtrbmasuk', document.getElementById('hrgjual_dtrbmasuk').value || '0');
        form.append('hrgjual_dtrbmasuk_resep', document.getElementById('hrgjual_dtrbmasuk_resep').value || '0');
        form.append('hrgjual_dtrbmasuk_nakes', document.getElementById('hrgjual_dtrbmasuk_nakes').value || '0');
        form.append('no_batch', noBatch);
        form.append('exp_date', document.getElementById('exp_date').value);

        fetch("{{ route('inventory.byrkredit.detail.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function() {
                ['id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk', 'sat_dtrbmasuk', 'no_batch',
                    'hrgsat_dtrbmasuk', 'hrgjual_dtrbmasuk', 'hrgjual_dtrbmasuk_resep', 'hrgjual_dtrbmasuk_nakes'
                ].forEach(function(id) { document.getElementById(id).value = ''; });
                loadTabelDetail();
            });
    }

    // --- Simpan Transaksi (finalisasi header) ---
    function simpanTransaksi() {
        var nmSupplier = document.getElementById('nm_supplier').value;
        if (nmSupplier === '') { alert('Belum ada data supplier'); return; }

        var carabayarEl = document.getElementById('carabayar');
        if (!carabayarEl) { alert('Tabel item belum selesai dimuat, coba lagi sebentar.'); return; }

        var form = new FormData();
        form.append('_token', '{{ csrf_token() }}');
        form.append('_method', 'PUT');
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('tgl_trbmasuk', document.getElementById('tgl_trbmasuk').value);
        form.append('id_supplier', document.getElementById('id_supplier').value);
        form.append('nm_supplier', nmSupplier);
        form.append('tlp_supplier', document.getElementById('tlp_supplier').value);
        form.append('alamat_trbmasuk', document.getElementById('alamat_trbmasuk').value);
        form.append('ket_trbmasuk', document.getElementById('ket_trbmasuk').value);
        form.append('carabayar', carabayarEl.value);
        form.append('jatuhtempo', document.getElementById('jatuhtempo').value || '');
        form.append('ttl_trbmasuk', (document.getElementById('ttl_trkasir').value || '0').replace(/\./g, ''));
        form.append('dp_bayar', (document.getElementById('diskon_faktur_nominal').value || '0').replace(/\./g, ''));
        form.append('sisa_bayar', (document.getElementById('sisa_bayar').value || '0').replace(/\./g, ''));

        fetch("{{ route('inventory.byrkredit.update', $trbmasuk->id_trbmasuk) }}", { method: 'POST', body: form })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menyimpan transaksi');
                window.location = "{{ route('inventory.byrkredit.index') }}";
            })
            .catch(function() { alert('Proses gagal, periksa kembali data yang diisi.'); });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F3') { event.preventDefault(); simpanTransaksi(); }
    });
</script>
@endpush
