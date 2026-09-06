@extends('inventory.layouts.app')

@section('header', 'Detail Jenis Penjualan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Transaksi Berdasarkan Jenis Penjualan</h3>
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
                        <label>Jenis Penjualan</label>
                        <select class="form-control" id="tipe">
                            <option value="1">Reguler</option>
                            <option value="2">Resep</option>
                            <option value="3">Nakes</option>
                            <option value="7">SEMUA</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" id="btnCetak">Cetak PDF</button>
                <button type="button" class="btn btn-success" id="btnExcel">Export Excel</button>
                <a class="btn btn-danger" href="{{ route('inventory.index') }}">KEMBALI</a>
            </div>
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
            tipe: document.getElementById('tipe').value,
        }).toString();
        window.open("{{ route('inventory.labajenisobat.cetak') }}?" + qs, '_blank');
    });
    document.getElementById('btnExcel').addEventListener('click', function() {
        if (!tanggalValid()) return;
        var qs = new URLSearchParams({
            tgl_awal: document.getElementById('tgl_awal').value,
            tgl_akhir: document.getElementById('tgl_akhir').value,
            tipe: document.getElementById('tipe').value,
        }).toString();
        window.open("{{ route('inventory.labajenisobat.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
