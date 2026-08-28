@php $obat = $obat ?? []; @endphp
<div class="obat-row border rounded p-2 mb-2">
    <div class="row g-2 align-items-start">
        <div class="col-md-7">
            <div class="autocomplete-wrapper position-relative">
                <input type="hidden" name="obat_kd[]" class="obat-kd" value="{{ $obat['kd_barang'] ?? '' }}">
                <input type="text" name="obat_nama[]" class="form-control obat-nama"
                    placeholder="Nama obat (ketik untuk cari)" value="{{ $obat['nm_barang'] ?? '' }}"
                    autocomplete="off">
                <div class="autocomplete-panel list-group position-absolute w-100 shadow"
                    style="z-index:1000; max-height:220px; overflow-y:auto; display:none;"></div>
            </div>
        </div>
        <div class="col-md-4">
            <input type="text" name="aturan_pakai[]" class="form-control" placeholder="Aturan pakai"
                value="{{ $obat['aturan_pakai'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm btn-remove-obat">x</button>
        </div>
    </div>
</div>
