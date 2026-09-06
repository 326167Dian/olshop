@extends('inventory.layouts.app')

@php
    $meta = [
        'laku' => ['title' => 'BARANG LAKU', 'box' => 'box-primary', 'route' => 'inventory.mstok.laku.data'],
        'lancar' => ['title' => 'BARANG LANCAR', 'box' => 'box-success', 'route' => 'inventory.mstok.lancar.data'],
        'slow' => ['title' => 'BARANG SLOW', 'box' => 'box-warning', 'route' => 'inventory.mstok.slow.data'],
    ][$tab];
@endphp

@section('header', 'Nilai Stok & Traffic Barang')

@section('content')
    <div class="card {{ $meta['box'] }}">
        <div class="card-header">
            <h3 class="card-title">{{ $meta['title'] }}</h3>
        </div>
        <div class="card-body">
            <center><strong>MySIFA TRAFFIC ANALYSIS</strong></center>
            <br>
            <center>
                @if ($tab !== 'laku')
                    <a class="btn btn-primary btn-flat" href="{{ route('inventory.mstok.laku') }}">LAKU</a>
                @endif
                @if ($tab !== 'lancar')
                    <a class="btn btn-success btn-flat" href="{{ route('inventory.mstok.lancar') }}">LANCAR</a>
                @endif
                @if ($tab !== 'slow')
                    <a class="btn btn-warning btn-flat" href="{{ route('inventory.mstok.slow') }}">SLOW</a>
                @endif
                <a class="btn btn-info btn-flat" href="{{ route('inventory.mstok.index') }}">GLOBAL</a>
                <a class="btn btn-danger btn-flat" href="{{ route('inventory.mstok.macet') }}">MACET</a>
            </center>
            <br><br>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Awal</label>
                        <input type="date" class="form-control" id="tgl_awal" value="{{ $start }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tgl_akhir" value="{{ $finish }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="button" class="btn btn-primary" id="btnSubmit">SUBMIT</button>
                    </div>
                </div>
            </div>
            <hr>

            <div class="table-responsive">
                <table id="tabel-bucket" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Qty/Stok</th>
                            <th class="text-right">Buffer</th>
                            <th class="text-right">T30</th>
                            <th class="text-right">Q30</th>
                            <th class="text-right">OM30</th>
                            <th class="text-right">L30</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-right">Harga Beli</th>
                            <th class="text-center">Nilai Barang</th>
                            <th width="140">Kartu Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <h3><center>Total</center></h3>
                            </td>
                            <td colspan="6">
                                <h4>Total Stok Tersedia: Rp <span id="nilaiStok">0</span></h4>
                                <h4>Total Omzet: Rp <span id="nilaiOmset">0</span></h4>
                                <h4>Total Laba: Rp <span id="nilaiLaba">0</span></h4>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function formatRupiah(v) {
        return Math.round(v || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    document.getElementById('btnSubmit').addEventListener('click', function () {
        var qs = new URLSearchParams({
            start: document.getElementById('tgl_awal').value,
            finish: document.getElementById('tgl_akhir').value,
        }).toString();
        window.location.href = "{{ url()->current() }}?" + qs;
    });

    $(function () {
        var start = @json($start);
        var finish = @json($finish);

        $('#tabel-bucket').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route($meta['route']) }}?start=" + start + "&finish=" + finish,
                type: 'GET'
            },
            rowCallback: function (row, data) {
                if (data.t30 <= 0) {
                    $(row).find('td:eq(0)').css({ 'background-color': '#dd4b39', color: '#fff' });
                } else if (data.t30 <= 5) {
                    $(row).find('td:eq(0)').css({ 'background-color': '#f39c12', color: '#fff' });
                } else if (data.t30 <= 10) {
                    $(row).find('td:eq(0)').css({ 'background-color': '#00a65a', color: '#fff' });
                } else {
                    $(row).find('td:eq(0)').css('background-color', '#00c0ef');
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-right', searchable: false },
                { data: 'stok_buffer', className: 'text-right', searchable: false },
                { data: 't30', className: 'text-right', searchable: false },
                { data: 'q30', className: 'text-right', searchable: false },
                { data: 'om30', className: 'text-right', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'l30', className: 'text-right', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'satuan', className: 'text-center', searchable: false },
                { data: 'harga_beli', className: 'text-right', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'nilai_barang', className: 'text-center', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'kartu_stok', className: 'text-center', orderable: false, searchable: false },
            ],
            footerCallback: function () {
                var json = this.api().ajax.json();
                $('#nilaiStok').text(formatRupiah(json.totalStok));
                $('#nilaiOmset').text(formatRupiah(json.totalOm30));
                $('#nilaiLaba').text(formatRupiah(json.totalL30));
            }
        });
    });
</script>
@endpush
