@if ($bundle)
    @method('PUT')
    <input type="hidden" name="kd_bundle" value="{{ $bundle->kd_bundle }}">
@endif

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>Nama Paket Produk</label>
            <input type="text" name="nm_bundle" class="form-control" value="{{ old('nm_bundle', $bundle->nm_bundle ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label>Satuan</label>
            <select name="sat_bundle" class="form-control" required>
                <option value="">Pilih Satuan</option>
                @foreach ($satuanList as $s)
                    <option value="{{ $s->nm_satuan }}" @selected(old('sat_bundle', $bundle->sat_bundle ?? '') === $s->nm_satuan)>{{ $s->nm_satuan }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<hr>

<div class="table-responsive">
    <table class="table table-bordered" id="table-produk">
        <thead>
            <tr>
                <th width="3%" class="text-center">No.</th>
                <th>Nama Produk</th>
                <th style="width:110px">Qty</th>
                <th style="width:110px">Satuan</th>
                <th style="width:150px" class="text-center">Harga Jual</th>
                <th style="width:150px" class="text-center">Sub Total</th>
                <th style="width:70px" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="dynamic-field">
            @forelse ($bundle->detail ?? [] as $i => $d)
                <tr class="row-obat">
                    <td class="text-center nomor">{{ $i + 1 }}.</td>
                    <td>
                        <div class="autocomplete-wrapper" style="position:relative">
                            <input type="hidden" name="obat_id[]" class="obat-id" value="{{ $d->id_barang }}">
                            <input type="hidden" name="obat_kd[]" class="obat-kd" value="{{ $d->kd_barang }}">
                            <input type="text" name="obat_nama[]" class="form-control obat-nama" placeholder="Nama obat (ketik lalu Enter)" value="{{ $d->nm_barang }}">
                            <div class="autocomplete-panel"></div>
                        </div>
                    </td>
                    <td><input type="number" name="qty[]" class="form-control qty" min="1" value="{{ $d->qty_barang }}"></td>
                    <td><input type="text" name="sat_barang[]" class="form-control satuan" value="{{ $d->sat_barang }}" readonly></td>
                    <td><input type="number" step="1" name="hrgjual[]" class="form-control hrgjual" value="{{ (int) $d->hrgjual_barang }}" style="text-align:right"></td>
                    <td><input type="text" name="subtotal[]" class="form-control subtotal" value="{{ number_format($d->subtotal, 0, ',', '.') }}" readonly style="text-align:right"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-row" data-idbundle_detail="{{ $d->idbundle_detail }}"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr class="row-obat">
                    <td class="text-center nomor">1.</td>
                    <td>
                        <div class="autocomplete-wrapper" style="position:relative">
                            <input type="hidden" name="obat_id[]" class="obat-id">
                            <input type="hidden" name="obat_kd[]" class="obat-kd">
                            <input type="text" name="obat_nama[]" class="form-control obat-nama" placeholder="Nama obat (ketik lalu Enter)">
                            <div class="autocomplete-panel"></div>
                        </div>
                    </td>
                    <td><input type="number" name="qty[]" class="form-control qty" min="1" value="1"></td>
                    <td><input type="text" name="sat_barang[]" class="form-control satuan" readonly></td>
                    <td><input type="number" step="1" name="hrgjual[]" class="form-control hrgjual" style="text-align:right"></td>
                    <td><input type="text" name="subtotal[]" class="form-control subtotal" readonly style="text-align:right"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-row"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total Item :</td>
                <td colspan="2"><div id="total-item-display">0</div></td>
            </tr>
        </tfoot>
    </table>
    <button type="button" class="btn btn-default btn-sm" id="btn-tambah-row">Tambah Item</button>
</div>

<hr>

<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label>Jumlah Kuota</label>
            <input type="number" name="qty_bundle" class="form-control" min="1" value="{{ old('qty_bundle', $bundle->qty_bundle ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label>Harga Jual Bundling</label>
            <input type="number" step="1" name="hrgjual_bundle" id="hrgjual_bundle" class="form-control" min="0" value="{{ old('hrgjual_bundle', (int) ($bundle->hrgjual_bundle ?? 0)) }}" required>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
<a class="btn btn-default" href="{{ route('inventory.bundle.index') }}"><i class="fa fa-arrow-left"></i> Kembali</a>
