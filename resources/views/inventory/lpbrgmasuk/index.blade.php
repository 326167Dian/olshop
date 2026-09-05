@extends('inventory.layouts.app')

@section('header', 'Laporan Barang Masuk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Barang Masuk</h3>
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Supplier (opsional)</label>
                        <select class="form-control" id="id_supplier">
                            <option value="">-- Semua Supplier --</option>
                            @foreach ($supplierList as $s)
                                <option value="{{ $s->id_supplier }}">{{ $s->nm_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" id="btnCetak">Cetak PDF</button>
                <button type="button" class="btn btn-success" id="btnExcel">Export Excel</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function buildQuery() {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        var supplier = document.getElementById('id_supplier').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return null; }

        var params = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir });
        if (supplier) params.append('id_supplier', supplier);
        return params.toString();
    }

    document.getElementById('btnCetak').addEventListener('click', function() {
        var qs = buildQuery();
        if (qs === null) return;
        window.open("{{ route('inventory.lpbrgmasuk.cetak') }}?" + qs, '_blank');
    });
    document.getElementById('btnExcel').addEventListener('click', function() {
        var qs = buildQuery();
        if (qs === null) return;
        window.open("{{ route('inventory.lpbrgmasuk.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
