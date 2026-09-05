@extends('inventory.layouts.app')

@section('header', 'Laba Penjualan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Laba Penjualan Produk</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Awal</label>
                        <input type="date" class="form-control" id="tgl_awal" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tgl_akhir" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Shift (khusus Cetak PDF)</label>
                        <select class="form-control" id="shift">
                            <option value="1">Pagi</option>
                            <option value="2">Sore</option>
                            <option value="3">Malam</option>
                            <option value="0">Semua Shift</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" id="btnCetak">Cetak PDF</button>
                <button type="button" class="btn btn-success" id="btnExcel">Export Excel</button>
            </div>
            <p class="text-muted mt-2 mb-0">Export Excel selalu merangkum seluruh shift pada rentang tanggal (tidak ada filter shift), mengikuti aplikasi lama.</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function tanggalValid() {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return false; }
        return true;
    }

    document.getElementById('btnCetak').addEventListener('click', function() {
        if (!tanggalValid()) return;
        var qs = new URLSearchParams({
            tgl_awal: document.getElementById('tgl_awal').value,
            tgl_akhir: document.getElementById('tgl_akhir').value,
            shift: document.getElementById('shift').value,
        }).toString();
        window.open("{{ route('inventory.labapenjualan.cetak') }}?" + qs, '_blank');
    });
    document.getElementById('btnExcel').addEventListener('click', function() {
        if (!tanggalValid()) return;
        var qs = new URLSearchParams({
            tgl_awal: document.getElementById('tgl_awal').value,
            tgl_akhir: document.getElementById('tgl_akhir').value,
        }).toString();
        window.open("{{ route('inventory.labapenjualan.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
