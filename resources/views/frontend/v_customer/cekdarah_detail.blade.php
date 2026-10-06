@extends('frontend.layouts.index')
@section('content')
@php
    $angka = function ($value) {
        return is_numeric($value) ? (float) $value : null;
    };

    $glukosaPuasa = $angka($cekdarah->gula);
    $glukosa2pp = $angka($cekdarah->gula_2pp);
    $asamUrat = $angka($cekdarah->asamurat);
    $kolesterol = $angka($cekdarah->kolesterol);

    $tensiParts = explode('/', (string) $cekdarah->tensi);
    $sistolik = isset($tensiParts[0]) ? $angka(trim($tensiParts[0])) : null;
    $diastolik = isset($tensiParts[1]) ? $angka(trim($tensiParts[1])) : null;

    $glukosaPuasaAbnormal = $glukosaPuasa !== null && $glukosaPuasa > 125;
    $glukosa2ppAbnormal = $glukosa2pp !== null && $glukosa2pp > 200;
    $kolesterolAbnormal = $kolesterol !== null && $kolesterol > 200;
    $tensiAbnormal = ($sistolik !== null && $sistolik > 130) || ($diastolik !== null && $diastolik > 85);

    $asamUratBatas = null;
    $pelanggan = $cekdarah->pelanggan;

    if ($pelanggan && $pelanggan->tanggal_lahir) {
        $usia = \Illuminate\Support\Carbon::parse($pelanggan->tanggal_lahir)->age;
        $isPria = strtoupper($pelanggan->jenis_kelamin ?? '') === 'PRIA';

        if ($usia < 18) {
            $asamUratBatas = 5.5;
        } elseif ($usia <= 40) {
            $asamUratBatas = $isPria ? 7.5 : 6.5;
        } else {
            $asamUratBatas = $isPria ? 8.5 : 8;
        }
    }

    $asamUratAbnormal = $asamUrat !== null && $asamUratBatas !== null && $asamUrat > $asamUratBatas;

    $hasil = [
        ['label' => 'Glukosa Puasa', 'nilai' => $cekdarah->gula . ' mg/dl', 'abnormal' => $glukosaPuasaAbnormal],
        ['label' => 'Glukosa 2 PP', 'nilai' => $cekdarah->gula_2pp . ' mg/dl', 'abnormal' => $glukosa2ppAbnormal],
        ['label' => 'Asam Urat', 'nilai' => $cekdarah->asamurat . ' mg/dl', 'abnormal' => $asamUratAbnormal],
        ['label' => 'Kolesterol', 'nilai' => $cekdarah->kolesterol . ' mg/dl', 'abnormal' => $kolesterolAbnormal],
        ['label' => 'Tensi', 'nilai' => $cekdarah->tensi . ' mmHg', 'abnormal' => $tensiAbnormal],
    ];
@endphp

<style>
    .cekdarah-hasil-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 14px;
        border-bottom: 1px solid #eee;
    }

    .cekdarah-hasil-item:last-child {
        border-bottom: none;
    }

    .cekdarah-hasil-label {
        color: #555;
    }

    .cekdarah-hasil-nilai {
        font-weight: 600;
    }

    .cekdarah-hasil-nilai.abnormal {
        color: #d00000;
    }

    .cekdarah-badge-abnormal {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        background: #d00000;
        border-radius: 10px;
        padding: 1px 8px;
        margin-left: 8px;
        vertical-align: middle;
    }

    .cekdarah-ref-table {
        font-size: 12px;
        margin-bottom: 18px;
    }

    .cekdarah-ref-table caption {
        caption-side: top;
        font-weight: 600;
        color: #333;
        padding-bottom: 6px;
    }

    .cekdarah-ref-table th,
    .cekdarah-ref-table td {
        text-align: center;
        vertical-align: middle;
    }

    .cekdarah-ref-table td.label {
        text-align: left;
        font-weight: 500;
    }
</style>

<div class="section">
    <div class="container" style="max-width: 720px;">
        <div class="row">
            <div class="col-md-12">
                <div class="billing-details">
                    <div class="section-title">
                        <h3 class="title">Detail Hasil Cek Darah</h3>
                    </div>

                    <p class="text-muted mb-1">
                        Pasien: <strong>{{ $cekdarah->pelanggan->nm_pelanggan ?? '-' }}</strong>
                    </p>
                    <p class="text-muted mb-3">
                        Tanggal: {{ $cekdarah->waktu?->format('d-m-Y H:i') }}
                        @if ($cekdarah->petugas)
                            &bull; Petugas: {{ $cekdarah->petugas }}
                        @endif
                    </p>

                    <div class="card mb-4" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-body p-0">
                            @foreach ($hasil as $h)
                                <div class="cekdarah-hasil-item">
                                    <span class="cekdarah-hasil-label">{{ $h['label'] }}</span>
                                    <span>
                                        <span class="cekdarah-hasil-nilai {{ $h['abnormal'] ? 'abnormal' : '' }}">
                                            {{ $h['nilai'] }}
                                        </span>
                                        @if ($h['abnormal'])
                                            <span class="cekdarah-badge-abnormal">Tidak Normal</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <h5 class="mb-3">Tabel Acuan</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered cekdarah-ref-table">
                            <caption>Tabel Glukosa Darah (mg/dl)</caption>
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Normal</th>
                                    <th>Pre DM</th>
                                    <th>DM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label">Puasa</td>
                                    <td>70-100</td>
                                    <td>100-124</td>
                                    <td>&gt;125</td>
                                </tr>
                                <tr>
                                    <td class="label">2 PP</td>
                                    <td>&lt;140</td>
                                    <td>140-200</td>
                                    <td>&gt;200</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered cekdarah-ref-table">
                            <caption>Tabel Asam Urat (mg/dl)</caption>
                            <thead>
                                <tr>
                                    <th>Usia</th>
                                    <th>10-18</th>
                                    <th>18-40</th>
                                    <th>&gt;40</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label">Pria</td>
                                    <td>3.6-5.5</td>
                                    <td>2-7.5</td>
                                    <td>2-8.5</td>
                                </tr>
                                <tr>
                                    <td class="label">Wanita</td>
                                    <td>3.6-5.5</td>
                                    <td>2-6.5</td>
                                    <td>2-8</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered cekdarah-ref-table">
                            <caption>Tabel Kolesterol Total (mg/dl)</caption>
                            <thead>
                                <tr>
                                    <th>Kelamin</th>
                                    <th>Normal</th>
                                    <th>Pre Tinggi</th>
                                    <th>Tinggi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label">Pria/Wanita</td>
                                    <td>&lt;200</td>
                                    <td>200-239</td>
                                    <td>&gt;240</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered cekdarah-ref-table">
                            <caption>Tabel Tekanan Darah (mmHg)</caption>
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Sistolik</th>
                                    <th>Diastolik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="label">Optimal</td>
                                    <td>&lt;120</td>
                                    <td>&lt;80</td>
                                </tr>
                                <tr>
                                    <td class="label">Normal</td>
                                    <td>&lt;130</td>
                                    <td>&lt;85</td>
                                </tr>
                                <tr>
                                    <td class="label">Pre Hipertensi</td>
                                    <td>130-139</td>
                                    <td>85-89</td>
                                </tr>
                                <tr>
                                    <td class="label">Derajat 1</td>
                                    <td>140-159</td>
                                    <td>90-99</td>
                                </tr>
                                <tr>
                                    <td class="label">Derajat 2</td>
                                    <td>160-179</td>
                                    <td>100-109</td>
                                </tr>
                                <tr>
                                    <td class="label">Derajat 3</td>
                                    <td>&gt;180</td>
                                    <td>&gt;110</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a href="{{ route('customer.cekdarah.index') }}" class="primary-btn">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
