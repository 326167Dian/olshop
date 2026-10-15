@extends('inventory.layouts.app')

@section('header', 'Laporan Penjualan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Laporan Penjualan Produk</h3>
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
                        <label>Petugas</label>
                        <select class="form-control" id="id_user">
                            <option value="">ALL</option>
                            @foreach ($petugasList as $p)
                                <option value="{{ $p->id_admin }}">{{ $p->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Shift</label>
                        <select class="form-control" id="shift">
                            <option value="1">Pagi</option>
                            <option value="2">Sore</option>
                            <option value="3">Malam</option>
                            <option value="4">Semua Shift</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" id="btnTampil">Tampil</button>
                <button type="button" class="btn btn-success" id="btnExcel">Export Excel</button>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Perbandingan Penjualan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Periode 1</h6>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>Tanggal Awal</label>
                            <input type="date" class="form-control" id="cmp_tgl_awal_1" required>
                        </div>
                        <div class="col-6 form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" class="form-control" id="cmp_tgl_akhir_1" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Periode 2</h6>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>Tanggal Awal</label>
                            <input type="date" class="form-control" id="cmp_tgl_awal_2" required>
                        </div>
                        <div class="col-6 form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" class="form-control" id="cmp_tgl_akhir_2" required>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-2" id="btnBandingkan">Bandingkan</button>
            <div id="hasilPerbandingan" class="mt-3"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function buildQuery() {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        var shift = document.getElementById('shift').value;
        var idUser = document.getElementById('id_user').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return null; }

        var params = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir, shift: shift });
        if (idUser) params.append('id_user', idUser);
        return params.toString();
    }

    document.getElementById('btnTampil').addEventListener('click', function() {
        var qs = buildQuery();
        if (qs === null) return;
        window.open("{{ route('inventory.lpkasir.tampil') }}?" + qs, '_blank');
    });
    document.getElementById('btnExcel').addEventListener('click', function() {
        var qs = buildQuery();
        if (qs === null) return;
        window.open("{{ route('inventory.lpkasir.excel') }}?" + qs, '_blank');
    });

    document.getElementById('btnBandingkan').addEventListener('click', function() {
        var a1 = document.getElementById('cmp_tgl_awal_1').value;
        var b1 = document.getElementById('cmp_tgl_akhir_1').value;
        var a2 = document.getElementById('cmp_tgl_awal_2').value;
        var b2 = document.getElementById('cmp_tgl_akhir_2').value;
        if (!a1 || !b1 || !a2 || !b2) { alert('Tanggal awal dan akhir Periode 1 & Periode 2 wajib diisi.'); return; }

        var params = new URLSearchParams({
            tgl_awal_1: a1, tgl_akhir_1: b1,
            tgl_awal_2: a2, tgl_akhir_2: b2,
        });

        fetch("{{ route('inventory.lpkasir.perbandingan') }}?" + params.toString(), { cache: 'no-store' })
            .then(function(res) { return res.text(); })
            .then(function(html) { document.getElementById('hasilPerbandingan').innerHTML = html; });
    });
</script>
@endpush
