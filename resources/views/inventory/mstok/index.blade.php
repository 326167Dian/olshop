@extends('inventory.layouts.app')

@section('header', 'Nilai Stok & Traffic Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">NILAI DAN KATEGORI BARANG</h3>
        </div>
        <div class="card-body">
            <center><strong>MySIFA TRAFFIC ANALYSIS</strong></center>
            <br>
            <center>
                <a class="btn btn-primary btn-flat" href="{{ route('inventory.mstok.laku') }}">LAKU</a>
                <a class="btn btn-success btn-flat" href="{{ route('inventory.mstok.lancar') }}">LANCAR</a>
                <a class="btn btn-warning btn-flat" href="{{ route('inventory.mstok.slow') }}">SLOW</a>
                <a class="btn btn-danger btn-flat" href="{{ route('inventory.mstok.macet') }}">MACET</a>
            </center>
            <br><br>

            <div class="table-responsive">
                <table id="tabel-global" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Qty/Stok</th>
                            <th class="text-right">T30</th>
                            <th class="text-right">T60</th>
                            <th class="text-right">gr(%)</th>
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
                            <td colspan="7">
                                <h3><center>Total</center></h3>
                            </td>
                            <td colspan="5">
                                <h3><strong>Rp <span id="totalRupiah">0</span>,-</strong></h3>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <form id="form-recompute" method="POST" action="{{ route('inventory.mstok.recompute') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Proses update database T30/T60/GR/Q30 untuk seluruh item yang pernah diterima? Ini akan menjalankan satu query agregat, aman dijalankan berulang.');">
                    PROSES UPDATE DATABASE
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function formatRupiah(v) {
        return Math.round(v || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $(function () {
        $('#tabel-global').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('inventory.mstok.data') }}",
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
                if (data.gr < -30) {
                    $(row).find('td:eq(6)').css({ 'background-color': '#dd4b39', color: '#fff' });
                } else if (data.gr < 0) {
                    $(row).find('td:eq(6)').css({ 'background-color': '#f39c12', color: '#fff' });
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'kd_barang' },
                { data: 'nm_barang' },
                { data: 'stok_barang', className: 'text-right', searchable: false },
                { data: 't30', className: 'text-right', searchable: false },
                { data: 't60', className: 'text-right', searchable: false },
                { data: 'gr', className: 'text-right', searchable: false },
                { data: 'q30', className: 'text-right', searchable: false },
                { data: 'satuan', className: 'text-center', searchable: false },
                { data: 'harga_beli', className: 'text-right', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'nilai_barang', className: 'text-center', searchable: false, render: (d) => formatRupiah(d) },
                { data: 'kartu_stok', className: 'text-center', orderable: false, searchable: false },
            ],
            footerCallback: function () {
                var json = this.api().ajax.json();
                $('#totalRupiah').text(formatRupiah(json.totalStok));
            }
        });
    });
</script>
@endpush
