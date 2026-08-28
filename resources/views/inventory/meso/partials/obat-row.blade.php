@php $obat = $obat ?? []; @endphp
<div class="obat-row border rounded p-3 mb-2">
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Nama Obat</label>
            <input type="text" name="obat_nama[]" class="form-control" placeholder="Nama Dagang/Generik"
                value="{{ $obat['nama'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Bentuk Sediaan</label>
            <input type="text" name="obat_bentuk[]" class="form-control" value="{{ $obat['bentuk'] ?? '' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>No. Batch</label>
            <input type="text" name="obat_batch[]" class="form-control" value="{{ $obat['batch'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Cara Pemberian</label>
            <input type="text" name="obat_cara[]" class="form-control" value="{{ $obat['cara'] ?? '' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Dosis</label>
            <input type="text" name="obat_dosis[]" class="form-control" value="{{ $obat['dosis'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Indikasi Penggunaan</label>
            <input type="text" name="obat_indikasi[]" class="form-control" value="{{ $obat['indikasi'] ?? '' }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Tanggal Mula</label>
            <input type="date" name="obat_tgl_mula[]" class="form-control" value="{{ $obat['tgl_mula'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Tanggal Akhir</label>
            <input type="date" name="obat_tgl_akhir[]" class="form-control" value="{{ $obat['tgl_akhir'] ?? '' }}">
        </div>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="obat_jkn[]" value="1" {{ !empty($obat['jkn']) ? 'checked' : '' }}>
        <label class="form-check-label">Obat JKN</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" name="obat_dicurigai[]" value="1" {{ !empty($obat['dicurigai']) ? 'checked' : '' }}>
        <label class="form-check-label">Obat yang Dicurigai</label>
    </div>
    <button type="button" class="btn btn-danger btn-sm btn-remove-obat float-end">Hapus Baris</button>
</div>
