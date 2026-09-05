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
</script>
@endpush
