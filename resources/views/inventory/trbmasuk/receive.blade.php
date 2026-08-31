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
                        <label>Keterangan</label>
                        <input type="text" class="form-control" id="ket_trbmasuk" value="{{ $order->ket_trbmasuk }}">
                    </div>
                    <div class="form-group">
                        <label>Cara Bayar</label>
                        <select class="form-control" id="carabayar">
                            <option value="LUNAS">LUNAS</option>
                            <option value="KREDIT">KREDIT</option>
                        </select>
                    </div>
                    <div class="form-group" id="groupJatuhTempo" style="display:none;">
                        <label>Jatuh Tempo</label>
                        <input type="date" class="form-control" id="jatuhtempo">
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="simpanTransaksi()">Simpan Transaksi</button>
                        <a href="{{ route('inventory.trbmasuk.orders.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="text-muted">Edit langsung kolom Batch, Diskon, Kadaluarsa, Harga Beli, Harga Jual, Konversi, atau
                        Qty Grosir pada tabel di bawah untuk menerima tiap item pesanan. Item baru akan tersimpan otomatis saat
                        kolom pertama kali diedit.</p>
                </div>
            </div>

            <hr>
            <div id="tabeldata"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function loadTabelDetail() {
        var kd = document.getElementById('kd_trbmasuk').value;
        var kdOrders = document.getElementById('kd_orders').value;
        fetch("{{ route('inventory.trbmasuk.receive.detail.index') }}?kd_trbmasuk=" + encodeURIComponent(kd) +
                "&kd_orders=" + encodeURIComponent(kdOrders), { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) { $('#tabeldata').html(html); });
    }

    document.addEventListener('DOMContentLoaded', loadTabelDetail);

    document.getElementById('carabayar').addEventListener('change', function() {
        document.getElementById('groupJatuhTempo').style.display = this.value === 'KREDIT' ? '' : 'none';
    });

    function simpanTransaksi() {
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
        form.append('carabayar', document.getElementById('carabayar').value);
        form.append('jatuhtempo', document.getElementById('jatuhtempo').value || '');
        form.append('ttl_trbmasuk', '0');
        form.append('dp_bayar', '0');
        form.append('sisa_bayar', '0');

        fetch("{{ route('inventory.trbmasuk.store-from-order') }}", { method: 'POST', body: form })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menyimpan transaksi');
                window.location = "{{ route('inventory.trbmasuk.orders.index') }}";
            })
            .catch(function() { alert('Proses gagal, periksa kembali data yang diisi.'); });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F3') { event.preventDefault(); simpanTransaksi(); }
    });
</script>
@endpush
