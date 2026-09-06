@extends('inventory.layouts.app')

@section('header', 'Laporan Stok Opname')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LAPORAN STOK OPNAME</h3>
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
                        <label>Waktu Stok Opname</label>
                        <select class="form-control" id="shift">
                            <option value="0">SO BULANAN</option>
                            <option value="1">Pagi</option>
                            <option value="2">Sore</option>
                            <option value="3">Malam</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary" id="btnTampil">TAMPIL</button>
            <button type="button" class="btn btn-success" id="btnExcel"><i class="fa fa-fw fa-file-excel-o"></i> EXPORT EXCEL</button>

            <hr>
            <p class="text-center">Ringkasan Stok Opname 3 bulan yang lalu</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal SO</th>
                            <th>Jenis SO</th>
                            <th>Minus</th>
                            <th>Lebih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $labelJenis = [0 => 'SO BULANAN', 1 => 'PAGI', 2 => 'SORE', 3 => 'MALAM'];
                        @endphp
                        @forelse ($ringkasan as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->tgl_stokopname }}</td>
                                <td>{{ $labelJenis[$r->shift] ?? $r->shift }}</td>
                                <td>{{ number_format((float) $r->minus, 0, ',', '.') }}</td>
                                <td>{{ number_format((float) $r->plus, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data stok opname dalam 3 bulan terakhir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnTampil').addEventListener('click', function () {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        var shift = document.getElementById('shift').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir, shift: shift }).toString();
        window.open("{{ route('inventory.lapstokopname.laporan') }}?" + qs, '_blank');
    });

    document.getElementById('btnExcel').addEventListener('click', function () {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        var shift = document.getElementById('shift').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir, shift: shift }).toString();
        window.open("{{ route('inventory.lapstokopname.excel') }}?" + qs, '_blank');
    });
</script>
@endpush
