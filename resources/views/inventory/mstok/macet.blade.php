@extends('inventory.layouts.app')

@section('header', 'Nilai Stok & Traffic Barang')

@section('content')
    <div class="card box-danger">
        <div class="card-header">
            <h3 class="card-title">BARANG MACET</h3>
        </div>
        <div class="card-body">
            <center><strong>MySIFA TRAFFIC ANALYSIS</strong></center>
            <br>
            <center>
                <a class="btn btn-primary btn-flat" href="{{ route('inventory.mstok.laku') }}">LAKU</a>
                <a class="btn btn-success btn-flat" href="{{ route('inventory.mstok.lancar') }}">LANCAR</a>
                <a class="btn btn-warning btn-flat" href="{{ route('inventory.mstok.slow') }}">SLOW</a>
                <a class="btn btn-info btn-flat" href="{{ route('inventory.mstok.index') }}">GLOBAL</a>
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
                <table id="tabel-macet" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Qty/Stok</th>
                            <th class="text-right">Buffer</th>
                            <th class="text-right">T30</th>
                            <th class="text-right">Q30</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-right">Harga Beli</th>
                            <th class="text-center">Nilai Barang</th>
                            <th width="140">Kartu Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <h3><center>Total</center></h3>
                            </td>
                            <td colspan="5">
                                <h3><strong>Rp <span id="nilaiStok">0</span>,-</strong></h3>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <a class="btn btn-success btn-flat" id="btnExcel" href="#" target="_blank">EXPORT TO EXCEL</a>
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
        window.location.href = "{{ route('inventory.mstok.macet') }}?" + qs;
    });

    $(function () {
        var start = @json($start);
        var finish = @json($finish);

        document.getElementById('btnExcel').href = "{{ route('inventory.mstok.macet.excel') }}?start=" + start + "&finish=" + finish;

        $('#tabel-macet').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.mstok.macet.data') }}?start=" + start + "&finish=" + finish,
                type: 'GET'
            },
            rowCallback: function (row) {
                $(row).find('td:eq(0)').css({ 'background-color': '#dd4b39', color: '#fff' });
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-right', searchable: false },
                { data: 'stok_buffer', className: 'text-right', searchable: false },
                { data: 't30', className: 'text-right', searchable: false },
                { data: 'q30', className: 'text-right', searchable: false },
                { data: 'satuan', className: 'text-center', searchable: false },
                { data: 'harga_beli', className: 'text-right', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'nilai_barang', className: 'text-center', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'kartu_stok', className: 'text-center', orderable: false, searchable: false },
            ],
            footerCallback: function () {
                var json = this.api().ajax.json();
                $('#nilaiStok').text(formatRupiah(json.totalStok));
            }
        });
    });
</script>
@endpush
