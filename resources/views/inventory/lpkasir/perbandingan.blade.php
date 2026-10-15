<div class="row">
    <div class="col-md-6">
        <h6 class="fw-bold">{{ $labelPeriode1 }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:40px">No</th>
                        <th>Nama Barang</th>
                        <th style="width:60px" class="text-center">Qty</th>
                        <th style="width:80px" class="text-center">Satuan</th>
                        <th style="width:110px" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periode1 as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row->nmbrg_dtrkasir }}</td>
                            <td class="text-center">{{ $row->qty_total }}</td>
                            <td class="text-center">{{ $row->sat_dtrkasir }}</td>
                            <td class="text-end">{{ number_format($row->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end">{{ number_format($totalPeriode1, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="fw-bold">{{ $labelPeriode2 }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:40px">No</th>
                        <th>Nama Barang</th>
                        <th style="width:60px" class="text-center">Qty</th>
                        <th style="width:80px" class="text-center">Satuan</th>
                        <th style="width:110px" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periode2 as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row->nmbrg_dtrkasir }}</td>
                            <td class="text-center">{{ $row->qty_total }}</td>
                            <td class="text-center">{{ $row->sat_dtrkasir }}</td>
                            <td class="text-end">{{ number_format($row->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end">{{ number_format($totalPeriode2, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if ($labelOmzetBesar)
    <hr class="my-3">
    <h6 class="fw-bold">
        Barang yang terjual di {{ $labelOmzetBesar }} (omzet lebih besar) tapi TIDAK terjual sama sekali di periode satunya
    </h6>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Nama Barang</th>
                    <th style="width:60px" class="text-center">Qty</th>
                    <th style="width:80px" class="text-center">Satuan</th>
                    <th style="width:110px" class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangEksklusif as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->nmbrg_dtrkasir }}</td>
                        <td class="text-center">{{ $row->qty_total }}</td>
                        <td class="text-center">{{ $row->sat_dtrkasir }}</td>
                        <td class="text-end">{{ number_format($row->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Semua barang di periode omzet lebih besar juga terjual di periode satunya.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
