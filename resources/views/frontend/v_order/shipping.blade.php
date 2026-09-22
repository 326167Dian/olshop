@extends('frontend.layouts.index')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
<style>
    .pin-map { height: 280px; width: 100%; border-radius: 6px; margin-top: 10px; }
</style>
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Masukan Alamat</h4>
                        <hr>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('order.update-ongkir') }}" method="POST" id="shipping-form">
                            @csrf

                            {{-- Metode Pengantaran --}}
                            <div class="mb-3">
                                <label class="form-label">Metode Pengantaran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_antar"
                                        id="metode_reguler" value="reguler" required
                                        {{ $regulerTersedia ? '' : 'disabled' }} onchange="recomputeQuote()">
                                    <label class="form-check-label" for="metode_reguler">
                                        Reguler <span class="text-muted">(diantar hari ini pukul 16:00)</span>
                                    </label>
                                    @unless ($regulerTersedia)
                                    <div class="text-danger small">Reguler tidak tersedia untuk saat ini (order sudah lewat jam 15:00, atau hari ini Minggu). Silakan pilih Express.</div>
                                    @endunless
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_antar"
                                        id="metode_express" value="express" required onchange="recomputeQuote()">
                                    <label class="form-check-label" for="metode_express">
                                        Express <span class="text-muted">(diantar maksimal 2 jam setelah pesanan, tarif 2x Reguler)</span>
                                    </label>
                                </div>

                                <strong>Note</strong>
                                <div class="alert alert-info mt-2" role="alert">
                                    {!! $companySetting->catatan ?? 'Tidak ada catatan pengiriman dari perusahaan.'
                                    !!}
                                </div>
                            </div>

                            {{-- Preview ongkir --}}
                            <div class="mb-3">
                                <div id="ongkir-preview" class="alert alert-secondary mb-0">
                                    Pilih metode pengantaran &amp; alamat untuk melihat estimasi ongkir.
                                </div>
                            </div>

                            {{-- Pilihan alamat --}}
                            <div class="mb-3">
                                <label class="form-label">Alamat Pengantaran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_alamat"
                                        id="alamat_rumah" value="rumah" required checked onchange="toggleAlamat()">
                                    <label class="form-check-label" for="alamat_rumah">
                                        Alamat Rumah <span class="text-muted">(sesuai data profil Anda)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_alamat"
                                        id="alamat_lain" value="lain" required onchange="toggleAlamat()">
                                    <label class="form-check-label" for="alamat_lain">
                                        Alamat Lain <span class="text-muted">(pilih dari alamat tersimpan, atau tambah baru)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Alamat Rumah --}}
                            <div id="blok-alamat-rumah" class="mb-3">
                                <label class="form-label">Alamat Rumah</label>
                                <p class="form-control-plaintext">{{ $customer->alamat ?? '-' }}</p>

                                @if ($customer->latitude === null || $customer->longitude === null)
                                <div class="alert alert-warning">
                                    Lokasi rumah Anda belum ditandai di peta. Silakan tandai supaya ongkir bisa dihitung.
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="gunakanLokasiSaya('peta-rumah', 'rumah_lat', 'rumah_lng')">
                                    <i class="fa fa-map-marker-alt"></i> Gunakan Lokasi Saya
                                </button>
                                <div id="peta-rumah" class="pin-map"></div>
                                <input type="hidden" name="rumah_lat" id="rumah_lat">
                                <input type="hidden" name="rumah_lng" id="rumah_lng">
                                @endif
                            </div>

                            {{-- Alamat Lain --}}
                            <div id="blok-alamat-lain" class="mb-3" style="display:none;">
                                <label for="alamat_lain_pilihan" class="form-label">Pilih Alamat</label>
                                <select name="alamat_lain_pilihan" id="alamat_lain_pilihan" class="form-control" onchange="toggleAlamatBaru(); recomputeQuote();">
                                    <option value="" disabled selected>-- Pilih Alamat --</option>
                                    @foreach ($alamatLain as $a)
                                    <option value="{{ $a->id }}" data-lat="{{ $a->latitude }}" data-lng="{{ $a->longitude }}">
                                        {{ $a->label }} &mdash; {{ \Illuminate\Support\Str::limit($a->alamat, 60) }}
                                    </option>
                                    @endforeach
                                    <option value="baru">+ Tambah Alamat Baru</option>
                                </select>

                                <div id="blok-alamat-baru" class="mt-3" style="display:none;">
                                    <div class="mb-2">
                                        <label for="alamat_baru_label" class="form-label">Label Alamat</label>
                                        <input type="text" name="alamat_baru_label" id="alamat_baru_label" class="form-control" placeholder="Contoh: Kantor, Rumah Orang Tua">
                                    </div>
                                    <div class="mb-2">
                                        <label for="alamat_baru_teks" class="form-label">Alamat Lengkap</label>
                                        <textarea name="alamat_baru_teks" id="alamat_baru_teks" class="form-control" rows="3" placeholder="Tulis alamat lengkap"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="gunakanLokasiSaya('peta-alamat-baru', 'alamat_baru_lat', 'alamat_baru_lng')">
                                        <i class="fa fa-map-marker-alt"></i> Gunakan Lokasi Saya
                                    </button>
                                    <div id="peta-alamat-baru" class="pin-map"></div>
                                    <input type="hidden" name="alamat_baru_lat" id="alamat_baru_lat">
                                    <input type="hidden" name="alamat_baru_lng" id="alamat_baru_lng">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="no_tlp" class="form-label">No Telepon Anda</label>
                                <input type="text" name="no_tlp" id="no_tlp" class="form-control"
                                    placeholder="No Telepon" value="{{ $customer->no_tlp }}" readonly>
                            </div>

                            <br><br>
                            <button type="submit" class="primary-btn" id="submit-btn">Select Payment</button>
                        </form>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

@php
    $tarifTiers = $lokasiAntar->map(function ($t) {
        return [
            'min' => (float) $t->jarak_min,
            'max' => (float) $t->jarak_max,
            'reguler' => (float) $t->biaya_antar,
            'express' => (float) $t->biaya_antar * 2,
        ];
    })->values();
    $pharmacyPin = ['lat' => $companySetting->kehadiran_lat, 'lng' => $companySetting->kehadiran_lng];
    $customerPin = ['lat' => $customer->latitude, 'lng' => $customer->longitude];
@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
    const PHARMACY = @json($pharmacyPin);
    const TARIF_TIERS = @json($tarifTiers);
    const MAX_RADIUS_KM = 5;
    const CUSTOMER_PIN = @json($customerPin);

    const PIN_MAPS = {};

    function initPinMap(containerId, latInputId, lngInputId) {
        if (PIN_MAPS[containerId]) {
            setTimeout(() => PIN_MAPS[containerId].map.invalidateSize(), 200);
            return PIN_MAPS[containerId];
        }

        const center = PHARMACY.lat ? [PHARMACY.lat, PHARMACY.lng] : [-2.5, 118];
        const zoom = PHARMACY.lat ? 14 : 5;

        const map = L.map(containerId).setView(center, zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker(center, { draggable: true }).addTo(map);

        function setLatLng(lat, lng) {
            document.getElementById(latInputId).value = lat;
            document.getElementById(lngInputId).value = lng;
            recomputeQuote();
        }

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            setLatLng(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            setLatLng(e.latlng.lat, e.latlng.lng);
        });

        setTimeout(() => map.invalidateSize(), 200);

        PIN_MAPS[containerId] = { map, marker, setLatLng };
        return PIN_MAPS[containerId];
    }

    function gunakanLokasiSaya(containerId, latInputId, lngInputId) {
        if (!navigator.geolocation) {
            alert('Browser ini tidak mendukung akses lokasi.');
            return;
        }

        navigator.geolocation.getCurrentPosition(function (pos) {
            const pin = initPinMap(containerId, latInputId, lngInputId);
            pin.map.setView([pos.coords.latitude, pos.coords.longitude], 16);
            pin.marker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
            pin.setLatLng(pos.coords.latitude, pos.coords.longitude);
        }, function (err) {
            alert('Gagal mengambil lokasi: ' + err.message);
        });
    }

    function toggleAlamat() {
        var lain = document.getElementById('alamat_lain').checked;
        document.getElementById('blok-alamat-rumah').style.display = lain ? 'none' : 'block';
        document.getElementById('blok-alamat-lain').style.display = lain ? 'block' : 'none';

        if (!lain && document.getElementById('peta-rumah')) {
            initPinMap('peta-rumah', 'rumah_lat', 'rumah_lng');
        }

        recomputeQuote();
    }

    function toggleAlamatBaru() {
        var pilihan = document.getElementById('alamat_lain_pilihan').value;
        var isBaru = pilihan === 'baru';
        document.getElementById('blok-alamat-baru').style.display = isBaru ? 'block' : 'none';

        if (isBaru) {
            initPinMap('peta-alamat-baru', 'alamat_baru_lat', 'alamat_baru_lng');
        }
    }

    function haversineKm(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function getActiveLatLng() {
        var tipeAlamat = document.querySelector('input[name=tipe_alamat]:checked')?.value;

        if (tipeAlamat === 'rumah') {
            if (CUSTOMER_PIN.lat) {
                return { lat: parseFloat(CUSTOMER_PIN.lat), lng: parseFloat(CUSTOMER_PIN.lng) };
            }
            var lat = document.getElementById('rumah_lat')?.value;
            var lng = document.getElementById('rumah_lng')?.value;
            return lat && lng ? { lat: parseFloat(lat), lng: parseFloat(lng) } : null;
        }

        if (tipeAlamat === 'lain') {
            var select = document.getElementById('alamat_lain_pilihan');
            var pilihan = select?.value;

            if (pilihan === 'baru') {
                var lat = document.getElementById('alamat_baru_lat')?.value;
                var lng = document.getElementById('alamat_baru_lng')?.value;
                return lat && lng ? { lat: parseFloat(lat), lng: parseFloat(lng) } : null;
            }

            if (pilihan) {
                var opt = select.options[select.selectedIndex];
                return { lat: parseFloat(opt.dataset.lat), lng: parseFloat(opt.dataset.lng) };
            }
        }

        return null;
    }

    function showPreview(text, isError) {
        var el = document.getElementById('ongkir-preview');
        el.textContent = text;
        el.className = 'alert mb-0 ' + (isError ? 'alert-danger' : 'alert-secondary');
    }

    function setSubmitEnabled(enabled) {
        document.getElementById('submit-btn').disabled = !enabled;
    }

    function recomputeQuote() {
        if (!PHARMACY.lat) {
            showPreview('Lokasi apotek belum diatur. Hubungi admin.', true);
            setSubmitEnabled(false);
            return;
        }

        var pos = getActiveLatLng();
        if (!pos) {
            showPreview('Pilih/tandai lokasi alamat terlebih dahulu.');
            setSubmitEnabled(true);
            return;
        }

        var km = haversineKm(PHARMACY.lat, PHARMACY.lng, pos.lat, pos.lng);

        if (km > MAX_RADIUS_KM) {
            showPreview('Alamat melebihi radius pengantaran maksimal ' + MAX_RADIUS_KM + ' km (jarak ' + km.toFixed(2) + ' km). Silakan pilih alamat lain.', true);
            setSubmitEnabled(false);
            return;
        }

        var tier = TARIF_TIERS.find(t => km >= t.min && km <= t.max);
        if (!tier) {
            showPreview('Belum ada tarif untuk jarak ' + km.toFixed(2) + ' km. Hubungi admin.', true);
            setSubmitEnabled(false);
            return;
        }

        var metode = document.querySelector('input[name=metode_antar]:checked')?.value;
        var biaya = metode === 'express' ? tier.express : tier.reguler;
        var labelMetode = metode === 'express' ? 'Express' : (metode === 'reguler' ? 'Reguler' : '-- pilih metode --');

        showPreview('Jarak ' + km.toFixed(2) + ' km — ' + labelMetode + ': Rp ' + biaya.toLocaleString('id-ID'));
        setSubmitEnabled(true);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('peta-rumah')) {
            initPinMap('peta-rumah', 'rumah_lat', 'rumah_lng');
        }
        recomputeQuote();
    });
</script>
@endsection
