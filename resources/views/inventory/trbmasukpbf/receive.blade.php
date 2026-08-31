@extends('inventory.layouts.app')

@section('header', 'Terima Barang dari Pesanan')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Terima Barang dari Pesanan — {{ $order->kd_trbmasuk }}</h3>
        </div>
        <div class="card-body">
            <input type="hidden" id="kd_trbmasuk" value="{{ $kdTransaksi }}">
            <input type="hidden" id="kd_orders" value="{{ $order->kd_trbmasuk }}">
            <input type="hidden" id="petugas" value="{{ $petugas }}">

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Tanggal Terima</label>
                        <input type="date" class="form-control" id="tgl_trbmasuk" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $kdTransaksi }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" class="form-control" value="{{ $order->nm_supplier }}" disabled>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" id="tlp_supplier" value="{{ $order->tlp_supplier }}">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" id="alamat_trbmasuk" rows="2">{{ $order->alamat_trbmasuk }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>No Faktur</label>
                        <textarea class="form-control" id="ket_trbmasuk" rows="2">{{ $order->ket_trbmasuk }}</textarea>
                    </div>
                    <div class="form-group" id="groupJatuhTempo">
                        <label>Jatuh Tempo</label>
                        <input type="date" class="form-control" id="jatuhtempo">
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="simpanTransaksi()">Simpan Transaksi</button>
                        <a href="{{ route('inventory.trbmasukpbf.orders.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="text-muted">Edit langsung kolom Batch, Diskon, Kadaluarsa, HNA, Harga Jual, Konversi, atau Qty
                        Grosir pada tabel di bawah untuk menerima tiap item pesanan. Item baru akan tersimpan otomatis saat
                        kolom pertama kali diedit. Item yang tidak jadi dikirim bisa dibatalkan lewat tombol "Batalkan".</p>

                    <hr>
                    <h6 class="fw-bold">Tambah Item (mis. barang datang terpecah jadi beberapa No. Batch)</h6>
                    <input type="hidden" id="tambah_id_barang">
                    <input type="hidden" id="tambah_stok_barang">

                    <div class="form-group">
                        <label>Tipe Barang</label>
                        <select class="form-control" id="tambah_tipe_barang">
                            <option value="reguler" selected>REGULER</option>
                            <option value="bonus">BONUS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="tambah_kd_barang" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalItemTambah"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <div class="form-group position-relative">
                        <label>Nama Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="tambah_nmbrg_dtrbmasuk" autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnEnterNamaTambah">Enter</button>
                        </div>
                        <div id="panelNamaBarangTambah" class="list-group position-absolute w-100 shadow"
                            style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Qty Grosir</label>
                                <input type="number" step="any" class="form-control" id="tambah_qty_grosir">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Satuan Grosir</label>
                                <select class="form-control" id="tambah_sat_grosir">
                                    @foreach ($satuanList ?? [] as $s)
                                        <option value="{{ $s->nm_satuan }}">{{ $s->nm_satuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konversi (ke satuan retail)</label>
                        <input type="number" step="any" class="form-control" id="tambah_konversi">
                    </div>
                    <div class="form-group">
                        <label>No. Batch</label>
                        <input type="text" class="form-control" id="tambah_no_batch" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Tgl. Kadaluarsa</label>
                        <input type="date" class="form-control" id="tambah_exp_date" value="{{ now()->addDays(720)->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>HNA (Harga Netto Apotek)</label>
                        <input type="text" class="form-control" id="tambah_hnasat_dtrbmasuk">
                    </div>
                    <div class="form-group">
                        <label>Diskon Produk (%)</label>
                        <input type="number" step="any" class="form-control" id="tambah_diskon" value="0">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual</label>
                        <input type="text" class="form-control" id="tambah_hrgjual_dtrbmasuk">
                    </div>
                    <button type="button" class="btn btn-success mt-2" onclick="simpanItemTambahan()">Simpan Detail</button>
                </div>
            </div>

            <hr>
            <div id="tabeldata"></div>
        </div>
    </div>

    <!-- Modal Item untuk panel Tambah Item -->
    <div class="modal fade" id="modalItemTambah" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Item Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table id="tabel-item-picker-tambah" class="table table-sm table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>HNA</th>
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
    function loadTabelDetail() {
        var kd = document.getElementById('kd_trbmasuk').value;
        var kdOrders = document.getElementById('kd_orders').value;
        fetch("{{ route('inventory.trbmasukpbf.receive.detail.index') }}?kd_trbmasuk=" + encodeURIComponent(kd) +
                "&kd_orders=" + encodeURIComponent(kdOrders), { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) { $('#tabeldata').html(html); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadTabelDetail();
    });

    // Cara Bayar sekarang berada di footer tabel (partial yang dimuat lewat AJAX), sama
    // seperti tata letak aslinya -- lihat partials/receive-detail-table.blade.php untuk
    // wiring toggle Jatuh Tempo dan perhitungan Total Harga+PPN/Diskon Faktur/Total Tagihan.
    function simpanTransaksi() {
        var carabayarEl = document.getElementById('carabayar');
        if (!carabayarEl) { alert('Tabel item belum selesai dimuat, coba lagi sebentar.'); return; }

        var form = new FormData();
        form.append('_token', '{{ csrf_token() }}');
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('kd_orders', document.getElementById('kd_orders').value);
        form.append('tgl_trbmasuk', document.getElementById('tgl_trbmasuk').value);
        form.append('id_supplier', '{{ $order->id_supplier }}');
        form.append('nm_supplier', '{{ $order->nm_supplier }}');
        form.append('tlp_supplier', document.getElementById('tlp_supplier').value);
        form.append('alamat_trbmasuk', document.getElementById('alamat_trbmasuk').value);
        form.append('ket_trbmasuk', document.getElementById('ket_trbmasuk').value);
        form.append('carabayar', carabayarEl.value);
        form.append('jatuhtempo', document.getElementById('jatuhtempo').value || '');
        form.append('ttl_trbmasuk', (document.getElementById('ttl_trkasir').value || '0').replace(/\./g, ''));
        form.append('dp_bayar', (document.getElementById('diskon_faktur_nominal').value || '0').replace(/\./g, ''));
        form.append('sisa_bayar', (document.getElementById('sisa_bayar').value || '0').replace(/\./g, ''));

        fetch("{{ route('inventory.trbmasukpbf.store-from-order') }}", { method: 'POST', body: form })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menyimpan transaksi');
                window.location = "{{ route('inventory.trbmasukpbf.orders.index') }}";
            })
            .catch(function() { alert('Proses gagal, periksa kembali data yang diisi.'); });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F3') { event.preventDefault(); simpanTransaksi(); }
    });

    // ==================== Panel "Tambah Item" ====================
    var itemPickerTambahTable = null;

    document.getElementById('modalItemTambah').addEventListener('show.bs.modal', function() {
        if (itemPickerTambahTable) {
            itemPickerTambahTable.destroy();
            $('#tabel-item-picker-tambah').empty();
        }
        itemPickerTambahTable = $('#tabel-item-picker-tambah').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventory.trbmasukpbf.item-picker') }}",
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-center' },
                { data: 'sat_barang', className: 'text-center' },
                { data: 'hna', className: 'text-end' },
                { data: 'hrgjual_barang', className: 'text-end' },
                { data: 'pilih', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });

    $(document).on('click', '#modalItemTambah .btn-pilih-barang', function() {
        resolveItemTambahByName($(this).data('nm_barang'));
        bootstrap.Modal.getInstance(document.getElementById('modalItemTambah')).hide();
    });

    function fillItemTambahForm(d) {
        document.getElementById('tambah_id_barang').value = d.id_barang;
        document.getElementById('tambah_kd_barang').value = d.kd_barang;
        document.getElementById('tambah_nmbrg_dtrbmasuk').value = d.nm_barang;
        document.getElementById('tambah_stok_barang').value = d.stok_barang;
        document.getElementById('tambah_sat_grosir').value = d.sat_grosir;
        document.getElementById('tambah_konversi').value = d.konversi;
        document.getElementById('tambah_hnasat_dtrbmasuk').value = d.hna;
        document.getElementById('tambah_hrgjual_dtrbmasuk').value = d.hrgjual_barang;
    }

    function resolveItemTambahByName(nama) {
        var form = new FormData();
        form.append('nm_barang', nama);
        fetch("{{ route('inventory.trbmasukpbf.item-resolve') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function(d) {
                if (d.id_barang) fillItemTambahForm(d);
                else alert(d.message || 'Barang tidak ditemukan');
            });
    }

    function resolveItemTambahByCode(kode) {
        var form = new FormData();
        form.append('kd_barang', kode);
        fetch("{{ route('inventory.trbmasukpbf.item-resolve') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function(d) {
                if (d.id_barang) fillItemTambahForm(d);
                else alert(d.message || 'Barang tidak ditemukan');
            });
    }

    document.getElementById('tambah_kd_barang').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); resolveItemTambahByCode(this.value); }
    });
    document.getElementById('btnEnterNamaTambah').addEventListener('click', function() {
        resolveItemTambahByName(document.getElementById('tambah_nmbrg_dtrbmasuk').value);
    });
    document.getElementById('tambah_nmbrg_dtrbmasuk').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); resolveItemTambahByName(this.value); }
    });

    var searchDelayTambah = null;
    document.getElementById('tambah_nmbrg_dtrbmasuk').addEventListener('input', function() {
        var input = this;
        var panel = document.getElementById('panelNamaBarangTambah');
        var keyword = input.value.trim();
        clearTimeout(searchDelayTambah);
        if (keyword.length < 2) { panel.style.display = 'none'; return; }
        searchDelayTambah = setTimeout(function() {
            var form = new FormData();
            form.append('query', keyword);
            fetch("{{ route('inventory.trbmasukpbf.item-search') }}", {
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
                            document.getElementById('tambah_nmbrg_dtrbmasuk').value = item.nm_barang;
                            panel.style.display = 'none';
                            resolveItemTambahByName(item.nm_barang);
                        });
                        panel.appendChild(div);
                    });
                    panel.style.display = 'block';
                });
        }, 300);
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#tambah_nmbrg_dtrbmasuk') && !e.target.closest('#panelNamaBarangTambah')) {
            document.getElementById('panelNamaBarangTambah').style.display = 'none';
        }
    });

    function simpanItemTambahan() {
        var nmbrg = document.getElementById('tambah_nmbrg_dtrbmasuk').value;
        var qtyGrosir = document.getElementById('tambah_qty_grosir').value;
        var hna = document.getElementById('tambah_hnasat_dtrbmasuk').value;
        var konversi = document.getElementById('tambah_konversi').value;
        var noBatch = document.getElementById('tambah_no_batch').value;

        if (nmbrg === '') { alert('Belum ada Item terpilih'); return; }
        if (noBatch === '') { alert('No. Batch tidak boleh kosong'); return; }
        if (qtyGrosir === '' || parseFloat(qtyGrosir) <= 0) { alert('Qty Grosir harus diisi'); return; }
        if (hna === '') { alert('HNA tidak boleh kosong'); return; }
        if (konversi === '' || parseFloat(konversi) <= 0) { alert('Konversi tidak boleh kosong'); return; }

        var form = new FormData();
        form.append('kd_trbmasuk', document.getElementById('kd_trbmasuk').value);
        form.append('kd_orders', document.getElementById('kd_orders').value);
        form.append('id_barang', document.getElementById('tambah_id_barang').value);
        form.append('kd_barang', document.getElementById('tambah_kd_barang').value);
        form.append('nmbrg_dtrbmasuk', nmbrg);
        form.append('qty_grosir', qtyGrosir);
        form.append('sat_grosir', document.getElementById('tambah_sat_grosir').value);
        form.append('konversi', konversi);
        form.append('hnasat_dtrbmasuk', hna);
        form.append('diskon', document.getElementById('tambah_diskon').value || '0');
        form.append('hrgjual_dtrbmasuk', document.getElementById('tambah_hrgjual_dtrbmasuk').value || '0');
        form.append('no_batch', noBatch);
        form.append('exp_date', document.getElementById('tambah_exp_date').value);
        form.append('tipe_barang', document.getElementById('tambah_tipe_barang').value);

        fetch("{{ route('inventory.trbmasukpbf.detail.store') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            })
            .then(function(res) { return res.json(); })
            .then(function() {
                ['tambah_id_barang', 'tambah_kd_barang', 'tambah_nmbrg_dtrbmasuk', 'tambah_qty_grosir',
                    'tambah_konversi', 'tambah_no_batch', 'tambah_hnasat_dtrbmasuk', 'tambah_hrgjual_dtrbmasuk'
                ].forEach(function(id) { document.getElementById(id).value = ''; });
                document.getElementById('tambah_diskon').value = '0';
                document.getElementById('tambah_tipe_barang').value = 'reguler';
                loadTabelDetail();
            });
    }
</script>
@endpush
