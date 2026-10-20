@extends('inventory.layouts.app')

@section('header', 'Penjualan/Kasir')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaksi Penjualan Hari Ini</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.trkasir.create') }}">(F4) Tambah Penjualan</a>
            <a class="btn btn-sm btn-warning mb-3" href="{{ route('inventory.trkasir.perubahan') }}" target="_blank">Perubahan Transaksi</a>
            @if (Auth::guard('admin')->user()?->isPemilik())
                <a class="btn btn-sm btn-outline-dark mb-3" href="{{ route('inventory.trkasir.undo-deleted.index') }}">Undo Transaksi Terhapus</a>
                <a class="btn btn-sm btn-outline-secondary mb-3" href="{{ route('inventory.trkasir.item-terhapus.index') }}">Item Penjualan Terhapus</a>
            @endif

            @if ($komisiGlobalText)
                <div class="mysifa-marquee mb-3">
                    <span class="fw-bold fs-5">{{ $komisiGlobalText }}</span>
                </div>
            @endif

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
                        @foreach ($carabayarList as $cb)
                            <tr>
                                <td>{{ $cb->nm_carabayar }}</td>
                                <td class="ringkasan-total" data-id="{{ $cb->id_carabayar }}">0</td>
                                <td class="ringkasan-pagi" data-id="{{ $cb->id_carabayar }}">0</td>
                                <td class="ringkasan-sore" data-id="{{ $cb->id_carabayar }}">0</td>
                            </tr>
                        @endforeach
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

            @if ($komisiProdukText)
                <div class="mysifa-marquee mt-3">
                    <span class="fw-bold fs-5">{{ $komisiProdukText }}</span>
                </div>
            @endif
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
                if (!json || !json.ringkasan) return;

                var totalPagi = 0, totalSore = 0;

                document.querySelectorAll('.ringkasan-total').forEach(function(el) {
                    var row = json.ringkasan[el.dataset.id] || { total: 0, pagi: 0, sore: 0 };
                    el.textContent = formatRupiahRingkasan(row.total);
                });
                document.querySelectorAll('.ringkasan-pagi').forEach(function(el) {
                    var row = json.ringkasan[el.dataset.id] || { total: 0, pagi: 0, sore: 0 };
                    el.textContent = formatRupiahRingkasan(row.pagi);
                    totalPagi += row.pagi || 0;
                });
                document.querySelectorAll('.ringkasan-sore').forEach(function(el) {
                    var row = json.ringkasan[el.dataset.id] || { total: 0, pagi: 0, sore: 0 };
                    el.textContent = formatRupiahRingkasan(row.sore);
                    totalSore += row.sore || 0;
                });

                document.getElementById('totalKasir').textContent = formatRupiahRingkasan(json.totalKasir);
                document.getElementById('totalKasirPagi').textContent = formatRupiahRingkasan(totalPagi);
                document.getElementById('totalKasirSore').textContent = formatRupiahRingkasan(totalSore);
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
