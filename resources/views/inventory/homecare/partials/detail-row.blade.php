@php $detail = $detail ?? []; @endphp
<div class="detail-row border rounded p-3 mb-2">
    <div class="row">
        <div class="col-md-3 form-group">
            <label>Tgl. Kunjungan</label>
            <input type="text" name="tgl_kunjungan[]" class="form-control" placeholder="DD/MM/YYYY" value="{{ $detail['tgl_kunjungan'] ?? '' }}">
        </div>
        <div class="col-md-8 form-group">
            <label>Catatan Apoteker</label>
            <textarea name="catatan_apoteker[]" class="form-control" rows="3">{{ $detail['catatan_apoteker'] ?? '' }}</textarea>
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-sm btn-remove-row">Hapus Baris</button>
</div>
