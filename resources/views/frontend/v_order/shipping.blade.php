@extends('frontend.layouts.index')

@section('content')
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
                        <form action="{{ route('order.update-ongkir') }}" method="POST">
                            @csrf

                            {{-- Metode Pengantaran --}}
                            <div class="mb-3">
                                <label class="form-label">Metode Pengantaran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_antar"
                                        id="metode_reguler" value="reguler" required onchange="toggleMetodeAntar()">
                                    <label class="form-check-label" for="metode_reguler">
                                        Gratis Ongkir <span class="text-muted">(diantar sesuai jam reguler)</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_antar"
                                        id="metode_express" value="express" required onchange="toggleMetodeAntar()">
                                    <label class="form-check-label" for="metode_express">
                                        Pengantaran Express <span class="text-muted">(langsung diantar setelah pembayaran)</span>
                                    </label>
                                </div>

                                <strong>Note</strong>
                                <div class="alert alert-info mt-2" role="alert">
                                    {!! $companySetting->catatan ?? 'Tidak ada catatan pengiriman dari perusahaan.'
                                    !!}
                                </div>
                            </div>

                            {{-- Biaya Pengantaran Express per Kelurahan --}}
                            <div class="mb-3" id="lokasi-antar-wrapper" style="display:none;">
                                <label for="lokasi_antar_id" class="form-label">Biaya Pengantaran Express per Kelurahan</label>
                                <select name="lokasi_antar_id" id="lokasi_antar_id" class="form-control"
                                    onchange="document.getElementById('biaya-antar-preview').textContent = this.options[this.selectedIndex].dataset.biaya ? 'Rp. ' + Number(this.options[this.selectedIndex].dataset.biaya).toLocaleString('id-ID') : '-';">
                                    <option value="" disabled selected>-- Pilih Kelurahan --</option>
                                    @foreach ($lokasiAntar as $lokasi)
                                    <option value="{{ $lokasi->id }}" data-biaya="{{ $lokasi->biaya_antar }}">
                                        {{ $lokasi->nama_kelurahan }} (Rp. {{ number_format($lokasi->biaya_antar, 0, ',', '.') }})
                                    </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Biaya antar akan ditambahkan ke tagihan: <span id="biaya-antar-preview">-</span></small>
                                @if ($lokasiAntar->isEmpty())
                                <div class="alert alert-warning mt-2 mb-0">Belum ada wilayah pengantaran express yang tersedia. Silakan hubungi apotek.</div>
                                @endif
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
                                        Alamat Lain <span class="text-muted">(tulis alamat lain, selama masih dalam area pengantaran)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Alamat --}}
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap Pengantaran</label>
                                <textarea name="alamat" id="alamat" class="form-control" rows="3" required
                                    readonly
                                    placeholder="Tulis alamat lengkap tujuan pengantaran (pastikan masih dalam area/kelurahan yang dipilih)">{{ $customer->alamat }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="no_tlp" class="form-label">No Telepon Anda</label>
                                <input type="text" name="no_tlp" id="no_tlp" class="form-control"
                                    placeholder="No Telepon" value="{{ $customer->no_tlp }}" readonly>
                            </div>

                            <br><br>
                            <button type="submit" class="primary-btn">Select Payment</button>
                        </form>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAlamat() {
        var lain = document.getElementById('alamat_lain').checked;
        var textarea = document.getElementById('alamat');

        textarea.readOnly = !lain;
        textarea.value = lain ? '' : @json($customer->alamat);

        if (lain) {
            textarea.focus();
        }
    }

    function toggleMetodeAntar() {
        var express = document.getElementById('metode_express').checked;
        var wrapper = document.getElementById('lokasi-antar-wrapper');
        var select = document.getElementById('lokasi_antar_id');

        wrapper.style.display = express ? 'block' : 'none';
        select.required = express;

        if (!express) {
            select.value = '';
            document.getElementById('biaya-antar-preview').textContent = '-';
        }
    }
</script>
@endsection