@extends('inventory.layouts.app')

@section('header', 'Ubah Penjualan')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Penjualan -- {{ $trkasir->kd_trkasir }}</h3>
        </div>
        <div class="card-body">
            <input type="hidden" id="kd_trkasir" value="{{ $trkasir->kd_trkasir }}">
            <input type="hidden" id="id_admin" value="{{ $admin->id_admin }}">
            <input type="hidden" id="id_pelanggan" value="{{ $trkasir->id_pelanggan ?? 0 }}">
            <input type="hidden" id="max_poin" value="0">
            {{-- Redeem poin transaksi ini SENGAJA tidak diedit lewat layar ini (lihat
                 catatan di update()) -- dibekukan ke nilai transaksi aslinya supaya
                 pratinjau Total Akhir tetap akurat. --}}
            <input type="hidden" id="redeem_poin" value="{{ $trkasir->redeem_poin ?? 0 }}">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="tgl_trkasir" value="{{ $trkasir->tgl_trkasir?->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Kasir (transaksi awal)</label>
                        <input type="text" class="form-control" value="{{ $trkasir->petugas }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Petugas Pelayanan</label>
                        <select class="form-control" id="id_user">
                            <option value="{{ $admin->id_admin }}" @selected((int) $trkasir->id_user === (int) $admin->id_admin)>{{ $admin->nama_lengkap }}</option>
                            @foreach ($petugasList as $p)
                                <option value="{{ $p->id_admin }}" @selected((int) $trkasir->id_user === (int) $p->id_admin)>{{ $p->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Order (opsional)</label>
                        <input type="text" class="form-control" id="kodetx" value="{{ $trkasir->kodetx }}">
                    </div>
                    <div class="form-group">
                        <label>Jenis Transaksi (item baru)</label>
                        <select class="form-control" id="jenistx">
                            <option value="1" @selected((int) $trkasir->jenistx === 1)>Reguler</option>
                            <option value="2" @selected((int) $trkasir->jenistx === 2)>Resep</option>
                            <option value="3" @selected((int) $trkasir->jenistx === 3)>Marketplace</option>
                        </select>
                        <small class="text-muted">Hanya berlaku untuk item yang ditambahkan sekarang -- jenis transaksi transaksi ini sendiri tidak diubah lewat layar ini.</small>
                    </div>
                    <div class="form-group">
                        <label>Pelanggan</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nm_pelanggan" value="{{ $trkasir->nm_pelanggan }}" readonly>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPelanggan"><i class="fa fa-search"></i></button>
                            <button type="button" class="btn btn-outline-danger" id="btnHapusPelanggan"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <input type="hidden" id="tlp_pelanggan" value="{{ $trkasir->tlp_pelanggan }}">
                    <input type="hidden" id="alamat_pelanggan" value="{{ $trkasir->alamat_pelanggan }}">
                    <div class="form-group">
                        <label>Keterangan / Dokter</label>
                        <input type="text" class="form-control" id="ket_trkasir" value="{{ $trkasir->ket_trkasir }}">
                    </div>
                </div>

                <div class="col-lg-6">
                    <input type="hidden" id="id_barang">
                    <input type="hidden" id="stok_barang">
                    <input type="hidden" id="is_bundle" value="0">

                    <div class="form-group">
                        <label>Kode Barang / Bundle</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="kd_barang" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalItem"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group position-relative">
                        <label>Nama Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="nmbrg_dtrkasir" autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnEnterNama">Enter</button>
                        </div>
                        <div id="panelNamaBarang" class="list-group position-absolute w-100 shadow" style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label>Stok Tersedia</label>
                        <input type="text" class="form-control" id="stok_barang_display" disabled>
                    </div>
                    <div class="form-group">
                        <label>Qty</label>
                        <input type="number" step="any" min="0.01" class="form-control" id="qty_dtrkasir">
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <input type="text" class="form-control" id="sat_dtrkasir">
                    </div>
                    <div class="form-group">
                        <label>No. Batch (kosongkan untuk otomatis FEFO)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="no_batch" autocomplete="off" maxlength="10">
                            <button type="button" class="btn btn-outline-secondary" id="btnCariBatch"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tgl. Kadaluarsa</label>
                        <input type="text" class="form-control" id="exp_date_display" disabled placeholder="otomatis mengikuti batch">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual</label>
                        <input type="text" class="form-control" id="hrgjual_dtrkasir">
                    </div>
                    <div class="form-group">
                        <label>Diskon (%)</label>
                        <input type="number" class="form-control" id="disc" value="0" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>Resep</label>
                        <select class="form-control" id="resep">
                            <option value="TIDAK">TIDAK</option>
                            <option value="YA">YA</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-success mt-2" id="btnTambahKeranjang">(F1) Tambah ke Keranjang</button>
                </div>
            </div>

            <hr>
            <div id="tabeldata"></div>

            <div class="mt-3">
                <button type="button" class="btn btn-primary" id="btnSimpanPerubahan">Simpan Perubahan</button>
                <a href="{{ route('inventory.trkasir.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </div>

    <!-- Modal Item (barang + bundle) -->
    <div class="modal fade" id="modalItem" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width:90%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Barang / Bundle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabBarang">Barang</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBundle" id="tabBundleLink">Bundle</a></li>
                    </ul>
                    <div class="tab-content mt-2">
                        <div class="tab-pane fade show active table-responsive" id="tabBarang">
                            <table id="tabel-item-picker" class="table table-sm table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Obat</th>
                                        <th>Nama Obat</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Komisi</th>
                                        <th>Indikasi</th>
                                        <th>Hrg Jual</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="tab-pane fade table-responsive" id="tabBundle">
                            <table id="tabel-bundle-picker" class="table table-sm table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Bundle</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Harga Jual</th>
                                        <th>Pilih</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Batch -->
    <div class="modal fade" id="modalBatch" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih No. Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table id="tabel-batch-picker" class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Batch</th>
                                <th>Tgl. Kadaluarsa</th>
                                <th>Sisa Stok</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pelanggan -->
    <div class="modal fade" id="modalPelanggan" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table id="tabel-pelanggan-picker" class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th>Poin</th>
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
    var bundlePickerTable = null;
    var pelangganPickerTable = null;

    function idAdmin() { return document.getElementById('id_admin').value; }
    function kdTrkasir() { return document.getElementById('kd_trkasir').value; }
    function parseAngka(v) { return parseFloat(String(v || '0').replace(/\./g, '').replace(/,/g, '.')) || 0; }
    function formatRupiah(v) { return Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

    // Nilai transaksi yang sudah ada (dari trkasir) -- dipakai untuk mengisi ulang
    // footer AJAX (diskon1/diskon2/id_carabayar/dp_bayar) setiap kali #tabeldata
    // dimuat ulang, karena partial itu sendiri (dipakai bersama layar Tambah
    // Penjualan) tidak tahu apa-apa soal transaksi yang sudah final.
    var existingHeader = {
        diskon1: {{ (float) ($trkasir->diskon1 ?? 0) }},
        diskon2: {{ (float) ($trkasir->diskon2 ?? 0) }},
        dp_bayar: {{ (float) ($trkasir->dp_bayar ?? 0) }},
        id_carabayar: {{ (int) ($trkasir->id_carabayar ?? 0) }},
    };

    function loadTabelDetail() {
        fetch("{{ route('inventory.trkasir.detail.index') }}?kd_trkasir=" + encodeURIComponent(kdTrkasir()), { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                document.getElementById('tabeldata').innerHTML = html;
                document.getElementById('diskon1').value = existingHeader.diskon1;
                document.getElementById('diskon2').value = existingHeader.diskon2;
                document.getElementById('dp_bayar').value = existingHeader.dp_bayar;
                if (existingHeader.id_carabayar) document.getElementById('id_carabayar').value = existingHeader.id_carabayar;
                recalcTotal();
            });
    }
    document.addEventListener('DOMContentLoaded', loadTabelDetail);

    function recalcTotal() {
        var subtotalEl = document.getElementById('subtotalDetail');
        if (!subtotalEl) return;
        var subtotal = parseAngka(subtotalEl.value);
        var diskon1El = document.getElementById('diskon1');
        var diskon2El = document.getElementById('diskon2');
        var dpBayarEl = document.getElementById('dp_bayar');
        if (!diskon1El || !diskon2El || !dpBayarEl) return;

        var diskon1 = parseAngka(diskon1El.value);
        var diskon2 = parseAngka(diskon2El.value);
        var redeemPoin = parseAngka(document.getElementById('redeem_poin').value);

        var total = subtotal * (1 - (diskon1 / 100)) - diskon2;
        total = Math.max(0, Math.round(total - redeemPoin));

        document.getElementById('totalAkhirDisplay').value = formatRupiah(total);

        var dpBayar = parseAngka(dpBayarEl.value);
        document.getElementById('kembalianDisplay').value = formatRupiah(dpBayar - total);
    }

    document.getElementById('tabeldata').addEventListener('input', function(e) {
        if (['diskon1', 'diskon2', 'dp_bayar'].includes(e.target.id)) recalcTotal();
    });

    // --- Item picker: tab Barang ---
    document.getElementById('modalItem').addEventListener('show.bs.modal', function() {
        if (itemPickerTable) { itemPickerTable.destroy(); $('#tabel-item-picker').empty(); }
        itemPickerTable = $('#tabel-item-picker').DataTable({
            processing: true, serverSide: true, ajax: "{{ route('inventory.trkasir.item-picker') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_barang' }, { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-center' }, { data: 'sat_barang', className: 'text-center' },
                { data: 'komisi', className: 'text-end' },
                { data: 'indikasi' },
                { data: 'hrgjual_barang', className: 'text-end' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });
    document.getElementById('tabBundleLink').addEventListener('shown.bs.tab', function() {
        if (bundlePickerTable) { bundlePickerTable.destroy(); $('#tabel-bundle-picker').empty(); }
        bundlePickerTable = $('#tabel-bundle-picker').DataTable({
            processing: true, serverSide: true, ajax: "{{ route('inventory.trkasir.bundle-picker') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_bundle' }, { data: 'nm_bundle' },
                { data: 'qty_bundle', className: 'text-center' }, { data: 'sat_bundle', className: 'text-center' },
                { data: 'hrgjual_bundle', className: 'text-end' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });

    $(document).on('click', '.btn-pilih-barang', function() {
        document.getElementById('is_bundle').value = '0';
        resolveItemByName($(this).data('nm_barang'));
        bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
    });
    $(document).on('click', '.btn-pilih-bundle', function() {
        document.getElementById('is_bundle').value = '1';
        resolveBundle($(this).data('kd_bundle'));
        bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
    });

    function fillItemForm(d) {
        document.getElementById('id_barang').value = d.id_barang || 0;
        document.getElementById('kd_barang').value = d.kd_barang;
        document.getElementById('nmbrg_dtrkasir').value = d.nm_barang;
        document.getElementById('stok_barang').value = d.stok_barang;
        document.getElementById('stok_barang_display').value = d.stok_barang + ' ' + d.sat_barang;
        document.getElementById('sat_dtrkasir').value = d.sat_barang;
        document.getElementById('hrgjual_dtrkasir').value = formatRupiah(d.hrgjual_barang);
        document.getElementById('no_batch').value = '';
        document.getElementById('exp_date_display').value = '';
    }

    var batchPickerTable = null;
    document.getElementById('btnCariBatch').addEventListener('click', function() {
        if (document.getElementById('is_bundle').value === '1') { alert('Bundle tidak memakai No. Batch manual.'); return; }
        var kdBarang = document.getElementById('kd_barang').value;
        if (!kdBarang) { alert('Pilih barang terlebih dahulu.'); return; }

        if (batchPickerTable) { batchPickerTable.destroy(); $('#tabel-batch-picker').empty(); }
        batchPickerTable = $('#tabel-batch-picker').DataTable({
            processing: true, serverSide: true,
            ajax: "{{ route('inventory.trkasir.batch-picker') }}?kd_barang=" + encodeURIComponent(kdBarang),
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'no_batch' }, { data: 'exp_date', className: 'text-center' },
                { data: 'sisa', className: 'text-center' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
        new bootstrap.Modal(document.getElementById('modalBatch')).show();
    });
    $(document).on('click', '.btn-pilih-batch', function() {
        var d = $(this).data();
        document.getElementById('no_batch').value = d.no_batch || '';
        document.getElementById('exp_date_display').value = d.exp_date || '';
        bootstrap.Modal.getInstance(document.getElementById('modalBatch')).hide();
    });

    function resolveItemByName(nama) {
        var form = new FormData();
        form.append('nm_barang', nama);
        form.append('jenistx', document.getElementById('jenistx').value);
        fetch("{{ route('inventory.trkasir.item-resolve') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
            .then(function(res) { return res.json(); })
            .then(function(d) { if (d.id_barang) fillItemForm(d); else alert(d.message || 'Barang tidak ditemukan'); });
    }
    function resolveItemByCode(kode) {
        var form = new FormData();
        form.append('kd_barang', kode);
        form.append('jenistx', document.getElementById('jenistx').value);
        fetch("{{ route('inventory.trkasir.item-resolve') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
            .then(function(res) { return res.json(); })
            .then(function(d) { if (d.id_barang) { document.getElementById('is_bundle').value = '0'; fillItemForm(d); } else alert(d.message || 'Barang tidak ditemukan'); });
    }
    function resolveBundle(kode) {
        var form = new FormData();
        form.append('kd_bundle', kode);
        fetch("{{ route('inventory.trkasir.bundle-resolve') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
            .then(function(res) { return res.json(); })
            .then(function(d) { if (d.kd_barang) fillItemForm(d); else alert(d.message || 'Bundle tidak ditemukan'); });
    }

    document.getElementById('kd_barang').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('is_bundle').value = this.value.indexOf('BUND') === 0 ? '1' : '0'; resolveItemByCode(this.value); }
    });
    document.getElementById('btnEnterNama').addEventListener('click', function() { resolveItemByName(document.getElementById('nmbrg_dtrkasir').value); });
    document.getElementById('nmbrg_dtrkasir').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); resolveItemByName(this.value); } });

    var searchDelay = null;
    document.getElementById('nmbrg_dtrkasir').addEventListener('input', function() {
        var input = this, panel = document.getElementById('panelNamaBarang'), keyword = input.value.trim();
        clearTimeout(searchDelay);
        if (keyword.length < 2) { panel.style.display = 'none'; return; }
        searchDelay = setTimeout(function() {
            var form = new FormData();
            form.append('query', keyword);
            fetch("{{ route('inventory.trkasir.item-search') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
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
                            document.getElementById('nmbrg_dtrkasir').value = item.nm_barang;
                            document.getElementById('is_bundle').value = '0';
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
        if (!e.target.closest('#nmbrg_dtrkasir') && !e.target.closest('#panelNamaBarang')) document.getElementById('panelNamaBarang').style.display = 'none';
    });

    function tambahKeranjang() {
        var nmbrg = document.getElementById('nmbrg_dtrkasir').value;
        var qty = document.getElementById('qty_dtrkasir').value;
        if (nmbrg === '') { alert('Belum ada Item/Bundle terpilih'); return; }
        if (!qty || parseFloat(qty) <= 0) { alert('Qty harus lebih dari 0'); return; }

        var form = new FormData();
        form.append('kd_trkasir', kdTrkasir());
        form.append('id_barang', document.getElementById('id_barang').value || 0);
        form.append('kd_barang', document.getElementById('kd_barang').value);
        form.append('nmbrg_dtrkasir', nmbrg);
        form.append('qty_dtrkasir', qty);
        form.append('sat_dtrkasir', document.getElementById('sat_dtrkasir').value);
        form.append('hrgjual_dtrkasir', parseAngka(document.getElementById('hrgjual_dtrkasir').value));
        form.append('disc', document.getElementById('disc').value || 0);
        form.append('resep', document.getElementById('resep').value);
        form.append('tipe', document.getElementById('jenistx').value);
        form.append('no_batch', document.getElementById('no_batch').value || '');
        form.append('id_user', document.getElementById('id_user').value || '');
        form.append('id_admin', idAdmin());

        fetch("{{ route('inventory.trkasir.detail.store') }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
            .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
            .then(function(r) {
                if (!r.ok) { alert(r.d.message || 'Gagal menambah item.'); return; }
                ['id_barang', 'kd_barang', 'nmbrg_dtrkasir', 'qty_dtrkasir', 'sat_dtrkasir', 'hrgjual_dtrkasir', 'stok_barang', 'stok_barang_display', 'no_batch', 'exp_date_display']
                    .forEach(function(id) { document.getElementById(id).value = ''; });
                document.getElementById('disc').value = 0;
                document.getElementById('resep').value = 'TIDAK';
                loadTabelDetail();
            });
    }
    document.getElementById('btnTambahKeranjang').addEventListener('click', tambahKeranjang);

    var inlineSaveTimer = null;
    document.getElementById('tabeldata').addEventListener('change', function(e) {
        if (e.target.classList.contains('inline-resep')) scheduleInlineSave(e.target.closest('tr'));
    });
    document.getElementById('tabeldata').addEventListener('keydown', function(e) {
        if (e.target.classList.contains('inline-qty') && e.key === 'Enter') { e.preventDefault(); saveInline(e.target.closest('tr')); }
    });
    document.getElementById('tabeldata').addEventListener('focusout', function(e) {
        if (e.target.classList.contains('inline-qty')) scheduleInlineSave(e.target.closest('tr'));
    });
    function scheduleInlineSave(tr) { clearTimeout(inlineSaveTimer); inlineSaveTimer = setTimeout(function() { saveInline(tr); }, 350); }

    function saveInline(tr) {
        var id = tr.getAttribute('data-id');
        var qty = tr.querySelector('.inline-qty').value;
        var resep = tr.querySelector('.inline-resep').value;
        if (!qty || parseFloat(qty) <= 0) return;

        fetch("{{ url('inventory/trkasir/detail') }}/" + id + "/qty", {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ qty_dtrkasir: qty, resep: resep, id_admin: idAdmin() }),
            })
            .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
            .then(function(r) { if (!r.ok) alert(r.d.message || 'Gagal menyimpan perubahan.'); loadTabelDetail(); });
    }

    document.getElementById('tabeldata').addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn-hapus-detail')) return;
        if (!confirm('Hapus item ini dari keranjang?')) return;
        var id = e.target.closest('tr').getAttribute('data-id');
        fetch("{{ url('inventory/trkasir/detail') }}/" + id + "?id_admin=" + encodeURIComponent(idAdmin()), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
            .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }); })
            .then(function(r) { if (!r.ok) alert(r.d.message || 'Gagal menghapus item.'); loadTabelDetail(); });
    });

    document.getElementById('modalPelanggan').addEventListener('show.bs.modal', function() {
        if (pelangganPickerTable) { pelangganPickerTable.destroy(); $('#tabel-pelanggan-picker').empty(); }
        pelangganPickerTable = $('#tabel-pelanggan-picker').DataTable({
            processing: true, serverSide: true, ajax: "{{ route('inventory.trkasir.pelanggan-picker') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nm_pelanggan' }, { data: 'tlp_pelanggan' }, { data: 'alamat_pelanggan' },
                { data: 'total_poin', className: 'text-center' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });
    $(document).on('click', '.btn-pilih-pelanggan', function() {
        var d = $(this).data();
        document.getElementById('id_pelanggan').value = d.id_pelanggan;
        document.getElementById('nm_pelanggan').value = d.nm_pelanggan;
        document.getElementById('tlp_pelanggan').value = d.tlp_pelanggan || '';
        document.getElementById('alamat_pelanggan').value = d.alamat_pelanggan || '';
        bootstrap.Modal.getInstance(document.getElementById('modalPelanggan')).hide();
    });
    document.getElementById('btnHapusPelanggan').addEventListener('click', function() {
        document.getElementById('id_pelanggan').value = 0;
        document.getElementById('nm_pelanggan').value = '';
        document.getElementById('tlp_pelanggan').value = '';
        document.getElementById('alamat_pelanggan').value = '';
    });

    // --- Simpan Perubahan (header saja -- item sudah tersimpan langsung lewat AJAX di atas) ---
    function simpanPerubahan() {
        var carabayarEl = document.getElementById('id_carabayar');
        if (!carabayarEl) { alert('Tabel item belum selesai dimuat, coba lagi sebentar.'); return; }

        var form = new FormData();
        form.append('_method', 'PUT');
        form.append('id_user', document.getElementById('id_user').value);
        form.append('tgl_trkasir', document.getElementById('tgl_trkasir').value);
        form.append('id_pelanggan', document.getElementById('id_pelanggan').value || 0);
        form.append('nm_pelanggan', document.getElementById('nm_pelanggan').value);
        form.append('tlp_pelanggan', document.getElementById('tlp_pelanggan').value);
        form.append('alamat_pelanggan', document.getElementById('alamat_pelanggan').value);
        form.append('kodetx', document.getElementById('kodetx').value);
        form.append('ket_trkasir', document.getElementById('ket_trkasir').value);
        form.append('id_carabayar', carabayarEl.value);
        form.append('diskon1', document.getElementById('diskon1').value || 0);
        form.append('diskon2', parseAngka(document.getElementById('diskon2').value));
        form.append('dp_bayar', parseAngka(document.getElementById('dp_bayar').value));

        fetch("{{ route('inventory.trkasir.update', $trkasir) }}", { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: form })
            .then(function(res) { return res.json().then(function(d) { return { ok: res.ok, d: d }; }).catch(function() { return { ok: res.ok, d: {} }; }); })
            .then(function(r) {
                if (!r.ok) { alert((r.d && r.d.message) || 'Proses gagal, periksa kembali data yang diisi.'); return; }
                window.location = "{{ route('inventory.trkasir.index') }}";
            });
    }
    document.getElementById('btnSimpanPerubahan').addEventListener('click', simpanPerubahan);
</script>
@endpush
