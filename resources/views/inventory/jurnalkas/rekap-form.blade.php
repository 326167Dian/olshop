@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REKAPITULASI</h3>
        </div>
        <div class="card-body">
            @include('inventory.jurnalkas.partials.nav')
            <br><br>

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
            </div>

            <button type="button" class="btn btn-primary" id="btnTampil">TAMPIL</button>
            <a class="btn btn-danger" href="{{ route('inventory.jurnalkas.index') }}">KEMBALI</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnTampil').addEventListener('click', function () {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir }).toString();
        window.location.href = "{{ route('inventory.jurnalkas.rekap.hasil') }}?" + qs;
    });
</script>
@endpush
