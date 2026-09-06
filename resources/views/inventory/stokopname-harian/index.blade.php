@extends('inventory.layouts.app')

@section('header', 'Stok Opname Harian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stok Opname Harian</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="tgl_awal" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Shift Petugas</label>
                        <select class="form-control" id="shift">
                            <option value="1">Pagi</option>
                            <option value="2">Sore</option>
                            <option value="3">Malam</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="btnTampil">TAMPIL</button>
            <button type="button" class="btn btn-success" id="btnExcel"><i class="fa fa-fw fa-file-excel-o"></i> EXPORT EXCEL</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnTampil').addEventListener('click', function () {
        var tglAwal = document.getElementById('tgl_awal').value;
        var shift = document.getElementById('shift').value;
        if (!tglAwal) { alert('Tanggal wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, shift: shift }).toString();
        window.open("{{ route('inventory.soharian.tampil') }}?" + qs, '_blank');
    });

    document.getElementById('btnExcel').addEventListener('click', function () {
        var tglAwal = document.getElementById('tgl_awal').value;
        var shift = document.getElementById('shift').value;
        if (!tglAwal) { alert('Tanggal wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, shift: shift }).toString();
        window.open("{{ route('inventory.soharian.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
