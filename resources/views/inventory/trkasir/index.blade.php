@extends('inventory.layouts.app')

@section('header', 'Penjualan/Kasir')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaksi Penjualan Hari Ini</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.trkasir.create') }}">(F4) Tambah Penjualan</a>

            <div class="table-responsive">
                <table id="tabel-trkasir" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Shift</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Petugas</th>
                            <th>Cara Bayar</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <h4 class="mt-4">Ringkasan Transaksi</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="150">Tipe Transaksi</th>
                            <th>Nilai Transaksi</th>
                            <th>Shift Pagi</th>
                            <th>Shift Sore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tunai</td>
                            <td id="totalTunai">0</td>
                            <td id="totalTunaiPagi">0</td>
                            <td id="totalTunaiSore">0</td>
                        </tr>
                        <tr>
                            <td>Transfer</td>
                            <td id="totalTransfer">0</td>
                            <td id="totalTransferPagi">0</td>
                            <td id="totalTransferSore">0</td>
                        </tr>
                        <tr>
                            <td>Tempo</td>
                            <td id="totalTempo">0</td>
                            <td id="totalTempoPagi">0</td>
                            <td id="totalTempoSore">0</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color:#00fafa; font-weight:bold;">
                            <td>TOTAL</td>
                            <td id="totalKasir">0</td>
                            <td id="totalKasirPagi">0</td>
                            <td id="totalKasirSore">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function formatRupiahRingkasan(v) {
        return Math.round(v || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $(function() {
        $('#tabel-trkasir').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trkasir.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_trkasir', name: 'kd_trkasir' },
                { data: 'shift_label', name: 'shift_label', className: 'text-center' },
                { data: 'tgl_trkasir', name: 'tgl_trkasir', className: 'text-center' },
                { data: 'nm_pelanggan', name: 'nm_pelanggan' },
                { data: 'petugas', name: 'petugas' },
                { data: 'nm_carabayar', name: 'nm_carabayar', className: 'text-center' },
                { data: 'ttl_trkasir', name: 'ttl_trkasir', className: 'text-end' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ],
            drawCallback: function() {
                var json = this.api().ajax.json();
                if (!json) return;

                document.getElementById('totalTunai').textContent = formatRupiahRingkasan(json.totalTunai);
                document.getElementById('totalTunaiPagi').textContent = formatRupiahRingkasan(json.totalTunaiPagi);
                document.getElementById('totalTunaiSore').textContent = formatRupiahRingkasan(json.totalTunaiSore);

                document.getElementById('totalTransfer').textContent = formatRupiahRingkasan(json.totalTransfer);
                document.getElementById('totalTransferPagi').textContent = formatRupiahRingkasan(json.totalTransferPagi);
                document.getElementById('totalTransferSore').textContent = formatRupiahRingkasan(json.totalTransferSore);

                document.getElementById('totalTempo').textContent = formatRupiahRingkasan(json.totalTempo);
                document.getElementById('totalTempoPagi').textContent = formatRupiahRingkasan(json.totalTempoPagi);
                document.getElementById('totalTempoSore').textContent = formatRupiahRingkasan(json.totalTempoSore);

                document.getElementById('totalKasir').textContent = formatRupiahRingkasan(json.totalKasir);
                document.getElementById('totalKasirPagi').textContent = formatRupiahRingkasan(json.totalTunaiPagi + json.totalTransferPagi + json.totalTempoPagi);
                document.getElementById('totalKasirSore').textContent = formatRupiahRingkasan(json.totalTunaiSore + json.totalTransferSore + json.totalTempoSore);
            }
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F4') {
            event.preventDefault();
            window.location = "{{ route('inventory.trkasir.create') }}";
        }
    });
</script>
@endpush
