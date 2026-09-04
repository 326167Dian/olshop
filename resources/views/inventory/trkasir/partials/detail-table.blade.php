<input type="hidden" id="subtotalDetail" value="{{ $subtotal }}">

<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped" id="tabelKeranjang">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th style="width:90px">Qty</th>
                <th>Satuan</th>
                <th>Batch</th>
                <th>Exp</th>
                <th>Harga</th>
                <th>Disc%</th>
                <th>Total</th>
                <th style="width:100px">Resep</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
                <tr data-id="{{ $row->id_dtrkasir }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->nmbrg_dtrkasir }}@if ($row->kd_bundle)
                            <span class="badge bg-secondary">Bundle: {{ $row->nm_bundle }}</span>
                        @endif
                    </td>
                    <td><input type="number" min="0.01" step="any" class="form-control form-control-sm inline-qty" value="{{ $row->qty_dtrkasir }}"></td>
                    <td>{{ $row->sat_dtrkasir }}</td>
                    <td>{{ $row->no_batch }}</td>
                    <td>{{ $row->exp_date?->format('Y-m-d') }}</td>
                    <td class="text-end">{{ number_format($row->hrgjual_dtrkasir, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $row->disc }}</td>
                    <td class="text-end total-cell">{{ number_format($row->hrgttl_dtrkasir, 0, ',', '.') }}</td>
                    <td>
                        <select class="form-select form-select-sm inline-resep">
                            <option value="TIDAK" @selected($row->resep === 'TIDAK')>TIDAK</option>
                            <option value="YA" @selected($row->resep === 'YA')>YA</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-xs btn-hapus-detail">Hapus</button></td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Belum ada item</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-3">
        <label>Diskon Faktur (%)</label>
        <input type="number" class="form-control" id="diskon1" value="0" min="0" max="100">
    </div>
    <div class="col-md-3">
        <label>Diskon Faktur (Nominal)</label>
        <input type="text" class="form-control" id="diskon2" value="0">
    </div>
    <div class="col-md-3">
        <label>Jenis Bayar</label>
        <select class="form-control" id="id_carabayar">
            @foreach ($carabayarList as $cb)
                <option value="{{ $cb->id_carabayar }}">{{ $cb->nm_carabayar }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label>Sub Total</label>
        <input type="text" class="form-control" id="subtotalDisplay" value="{{ number_format($subtotal, 0, ',', '.') }}" readonly>
    </div>
</div>
<div class="row mt-2">
    <div class="col-md-4">
        <label>Total Akhir</label>
        <input type="text" class="form-control fw-bold" id="totalAkhirDisplay" readonly>
    </div>
    <div class="col-md-4">
        <label>Jumlah Bayar</label>
        <input type="text" class="form-control" id="dp_bayar" value="0">
    </div>
    <div class="col-md-4">
        <label>Kembalian</label>
        <input type="text" class="form-control" id="kembalianDisplay" readonly>
    </div>
</div>
