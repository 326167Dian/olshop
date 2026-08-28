@php $detail = $detail ?? []; @endphp
<div class="detail-row border rounded p-3 mb-2">
    <div class="row">
        <div class="col-md-2 form-group">
            <label>Tanggal</label>
            <input type="text" name="tanggal[]" class="form-control" placeholder="DD/MM/YYYY" value="{{ $detail['tanggal'] ?? '' }}">
        </div>
        <div class="col-md-3 form-group">
            <label>Nama Dokter</label>
            <input type="text" name="nama_dokter[]" class="form-control" value="{{ $detail['nama_dokter'] ?? '' }}">
        </div>
        <div class="col-md-4 form-group">
            <label>Nama Obat/Dosis/Cara Pemberian</label>
            <textarea name="nama_obat_dosis[]" class="form-control" rows="3">{{ $detail['nama_obat_dosis'] ?? '' }}</textarea>
        </div>
        <div class="col-md-3 form-group">
            <label>Catatan</label>
            <textarea name="catatan[]" class="form-control" rows="3">{{ $detail['catatan'] ?? '' }}</textarea>
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-sm btn-remove-row">Hapus Baris</button>
</div>
