@extends('backend.layouts.app')

@section('title', 'Input Pixel')
@section('header', 'Pengaturan Meta Pixel')

@section('content')
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Input Pixel Meta (Facebook / Instagram Ads)</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="mb-3">
                        Status:
                        @if (($setting->meta_pixel_id ?? null) && ($setting->meta_pixel_aktif ?? false))
                            <span class="badge bg-success badge-success">Aktif</span>
                            <span class="ms-2 ml-2">Pixel ID: <strong>{{ $setting->meta_pixel_id }}</strong></span>
                        @elseif ($setting->meta_pixel_id ?? null)
                            <span class="badge bg-secondary badge-secondary">Nonaktif</span>
                            <span class="ms-2 ml-2">Pixel ID: <strong>{{ $setting->meta_pixel_id }}</strong></span>
                        @else
                            <span class="badge bg-warning badge-warning">Belum dipasang</span>
                        @endif
                    </div>

                    <form action="{{ route('meta-pixel.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Pixel ID atau kode Pixel</label>
                            <textarea name="meta_pixel" class="form-control" rows="4"
                                placeholder="Contoh: 1234567890123456&#10;atau tempel seluruh kode Pixel dari Meta Events Manager">{{ old('meta_pixel', $setting->meta_pixel_id ?? '') }}</textarea>
                            <small class="text-muted">Boleh isi angka Pixel ID saja, atau tempel seluruh kode — sistem
                                mengambil ID-nya otomatis. Kosongkan lalu simpan untuk menghapus Pixel.</small>
                            @error('meta_pixel')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="meta_pixel_aktif" value="0">
                            <input class="form-check-input" type="checkbox" name="meta_pixel_aktif" value="1"
                                id="meta_pixel_aktif"
                                {{ old('meta_pixel_aktif', $setting->meta_pixel_aktif ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="meta_pixel_aktif">Aktifkan Pixel di website toko</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cara mendapatkan Pixel ID</h5>
                </div>
                <div class="card-body">
                    <ol class="ps-3 pl-3 mb-3">
                        <li>Buka <strong>Meta Events Manager</strong> (business.facebook.com/events_manager).</li>
                        <li>Pilih <strong>Hubungkan sumber data → Web</strong>, beri nama, lalu buat Pixel/Dataset.</li>
                        <li>Salin angka <strong>Pixel ID</strong> (15–16 digit), tempel di kolom sebelah, lalu Simpan.</li>
                        <li>Uji di menu <strong>Test Events</strong> atau ekstensi Chrome <strong>Meta Pixel Helper</strong>.</li>
                    </ol>
                    <p class="mb-1"><strong>Event yang otomatis terkirim:</strong></p>
                    <small class="text-muted">PageView, ViewContent (lihat produk), AddToCart, InitiateCheckout,
                        Purchase (COD &amp; QRIS, beserta nilai Rupiah), Contact (klik WhatsApp),
                        CompleteRegistration (akun baru). Nama obat tidak dikirim ke Meta.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
