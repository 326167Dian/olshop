@extends('inventory.layouts.app')

@section('header', 'Stok Opname Bulanan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stok Opname Bulanan</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Jumlah barang dengan stok lebih dari 0 (x) : <b>{{ $jumlahStokAda }}</b> item
            </div>

            @if ($jenisobatAsing->isNotEmpty())
                <div class="alert alert-warning">
                    <b>Perhatian:</b> ditemukan barang dengan field <b>jenisobat</b> yang tidak terdaftar di tabel Jenis Obat / Rak Obat:
                    <ul class="mt-2">
                        @foreach ($jenisobatAsing as $ja)
                            <li>
                                Jenis Obat <b>{{ $ja->jenisobat === '' ? '(kosong)' : $ja->jenisobat }}</b> — {{ $ja->jumlah }} item
                                <details class="mt-1 mb-2 ml-3">
                                    <summary style="cursor:pointer;">Lihat daftar barang</summary>
                                    <ul>
                                        @foreach ($ja->items as $ib)
                                            <li>{{ $ib->kd_barang }} - {{ $ib->nm_barang }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($jenisobatTakTerpakai->isNotEmpty())
                <div class="alert alert-warning">
                    <b>Perhatian:</b> ditemukan Jenis Obat / Rak Obat di tabel Jenis Obat yang belum dipakai barang apa pun:
                    <ul class="mt-2">
                        @foreach ($jenisobatTakTerpakai as $jt)
                            <li><b>{{ $jt->jenisobat }}</b> — {{ $jt->ket }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="tgl_awal" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nama Rak Obat</label>
                        <select class="form-control" id="jenisobat">
                            @foreach ($daftarRak as $rak)
                                <option value="{{ $rak }}">{{ $rak }}</option>
                            @endforeach
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
        var jenisobat = document.getElementById('jenisobat').value;
        if (!tglAwal) { alert('Tanggal wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl: tglAwal, jenisobat: jenisobat }).toString();
        window.open("{{ route('inventory.stokopname.tampil') }}?" + qs, '_blank');
    });

    document.getElementById('btnExcel').addEventListener('click', function () {
        var jenisobat = document.getElementById('jenisobat').value;
        var qs = new URLSearchParams({ jenisobat: jenisobat }).toString();
        window.open("{{ route('inventory.stokopname.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
