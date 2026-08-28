<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pelaporan ESO</title>
    <style>
        :root {
            --bg-yellow: #ffff00;
            --border-color: #000;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            font-size: 12px;
        }

        .form-container {
            background-color: var(--bg-yellow);
            max-width: 900px;
            margin: 0 auto;
            border: 2px solid var(--border-color);
            padding: 10px;
        }

        .header {
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid var(--border-color);
            padding: 5px;
            vertical-align: top;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
        }

        input[type="text"][readonly] {
            background-color: #ffffcc;
            font-weight: bold;
        }

        .obat-table th {
            font-size: 10px;
            text-align: center;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .signature-area {
            text-align: center;
            margin-top: 30px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .form-container {
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>
    @php $data = $meso; $dataObat = $meso->data_obat ?? []; @endphp

    <div class="form-container">
        <div class="header">
            <strong>FORMULIR PELAPORAN EFEK SAMPING OBAT (ESO)</strong>
            <span>Kode Sumber Data : {{ $data->kode_sumber_data ?: '_________' }}</span>
        </div>

        <span class="section-title">PENDERITA</span>
        <table>
            <tr>
                <td>Nama (Singkatan): <input type="text" value="{{ $data->nama_singkat }}" readonly></td>
                <td>Umur: <input type="text" value="{{ $data->umur }}" readonly></td>
                <td>Suku: <input type="text" value="{{ $data->suku }}" readonly></td>
                <td>Berat Badan: <input type="text" value="{{ $data->berat_badan }}" readonly></td>
                <td>Pekerjaan: <input type="text" value="{{ $data->pekerjaan }}" readonly></td>
            </tr>
        </table>

        <div class="grid-container">
            <div class="checkbox-group">
                <strong>Kelamin (Beri Tanda &radic;):</strong>
                <label><input type="checkbox" {{ $data->jenis_kelamin == 'L' ? 'checked' : '' }} disabled> Pria</label>
                <label><input type="checkbox" {{ $data->jenis_kelamin == 'P' ? 'checked' : '' }} disabled> Wanita</label>
                <label><input type="checkbox" {{ $data->status_hamil == 'hamil' ? 'checked' : '' }} disabled> Hamil</label>
                <label><input type="checkbox" {{ $data->status_hamil == 'tidak_hamil' ? 'checked' : '' }} disabled> Tidak Hamil</label>
                <label><input type="checkbox" {{ $data->status_hamil == 'tidak_tahu' ? 'checked' : '' }} disabled> Tidak Tahu</label>
            </div>
            <div>
                <strong>Penyakit Utama:</strong>
                <textarea rows="3" readonly>{{ $data->penyakit_utama }}</textarea>
                <br><br>
                <strong>Penyakit / Kondisi Lain yang Menyertai:</strong>
                <div class="checkbox-group">
                    <label><input type="checkbox" {{ $data->gangguan_ginjal ? 'checked' : '' }} disabled> Gangguan Ginjal</label>
                    <label><input type="checkbox" {{ $data->gangguan_hati ? 'checked' : '' }} disabled> Gangguan Hati</label>
                    <label><input type="checkbox" {{ $data->alergi ? 'checked' : '' }} disabled> Alergi</label>
                    <label><input type="checkbox" {{ $data->kondisi_medis_lain ? 'checked' : '' }} disabled> Kondisi medis lainnya</label>
                    @if ($data->kondisi_medis_lain_ket)
                        <small style="display:block; margin-top:5px;">{{ $data->kondisi_medis_lain_ket }}</small>
                    @endif
                </div>
            </div>
            <div>
                <strong>Kesudahan Penyakit Utama:</strong>
                <div class="checkbox-group">
                    @foreach (['sembuh' => 'Sembuh', 'sembuh_gejala_sisa' => 'Sembuh dengan gejala sisa', 'belum_sembuh' => 'Belum sembuh', 'meninggal' => 'Meninggal', 'tidak_tahu' => 'Tidak Tahu'] as $val => $label)
                        <label><input type="checkbox" {{ $data->kesudahan_penyakit == $val ? 'checked' : '' }} disabled> {{ $label }}</label>
                    @endforeach
                </div>
            </div>
        </div>

        <span class="section-title">EFEK SAMPING OBAT</span>
        <table class="eso-table">
            <tr>
                <th width="30%">Bentuk / Manifestasi ESO yang Terjadi / Keluhan Lain</th>
                <th width="20%">Masalah pada Mutu/Kualitas Produk Obat</th>
                <th width="20%">Saat/Tanggal Mula Terjadi</th>
                <th width="30%">Kesudahan ESO (Beri Tanda &radic;)</th>
            </tr>
            <tr>
                <td><textarea rows="4" readonly>{{ $data->manifestasi_eso }}</textarea></td>
                <td><textarea rows="4" readonly>{{ $data->masalah_mutu_produk }}</textarea></td>
                <td><input type="text" value="{{ $data->tanggal_mula_eso?->format('d-m-Y') }}" readonly></td>
                <td>
                    <div class="checkbox-group">
                        @foreach (['sembuh' => 'Sembuh', 'sembuh_gejala_sisa' => 'Sembuh dengan gejala sisa', 'belum_sembuh' => 'Belum sembuh', 'meninggal' => 'Meninggal', 'tidak_tahu' => 'Tidak Tahu'] as $val => $label)
                            <label><input type="checkbox" {{ $data->kesudahan_eso == $val ? 'checked' : '' }} disabled> {{ $label }}</label>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
        <p>Riwayat ESO yang Pernah Dialami: <input type="text" value="{{ $data->riwayat_eso }}" readonly></p>

        <span class="section-title">OBAT</span>
        <table class="obat-table">
            <thead>
                <tr>
                    <th rowspan="2">Nama (Dagang/Generik)</th>
                    <th rowspan="2">Bentuk Sediaan</th>
                    <th rowspan="2">Obat JKN (&radic;)</th>
                    <th rowspan="2">No. Batch</th>
                    <th rowspan="2">Obat yang Dicurigai (&radic;)</th>
                    <th colspan="4">Pemberian</th>
                    <th rowspan="2">Indikasi Penggunaan</th>
                </tr>
                <tr>
                    <th>Cara</th>
                    <th>Dosis</th>
                    <th>Tgl Mula</th>
                    <th>Tgl Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataObat as $obat)
                    <tr>
                        <td><input type="text" value="{{ $obat['nama'] ?? '' }}" readonly></td>
                        <td><input type="text" value="{{ $obat['bentuk'] ?? '' }}" readonly></td>
                        <td><input type="checkbox" {{ !empty($obat['jkn']) ? 'checked' : '' }} disabled></td>
                        <td><input type="text" value="{{ $obat['batch'] ?? '' }}" readonly></td>
                        <td><input type="checkbox" {{ !empty($obat['dicurigai']) ? 'checked' : '' }} disabled></td>
                        <td><input type="text" value="{{ $obat['cara'] ?? '' }}" readonly></td>
                        <td><input type="text" value="{{ $obat['dosis'] ?? '' }}" readonly></td>
                        <td><input type="text" value="{{ $obat['tgl_mula'] ?? '' }}" readonly></td>
                        <td><input type="text" value="{{ $obat['tgl_akhir'] ?? '' }}" readonly></td>
                        <td><input type="text" value="{{ $obat['indikasi'] ?? '' }}" readonly></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center;">Tidak ada data obat</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-grid">
            <div>
                <strong>Keterangan Tambahan:</strong>
                <textarea rows="6" readonly>{{ $data->keterangan_tambahan }}</textarea>
            </div>
            <div>
                <div style="border: 1px solid #000; padding: 10px;">
                    <strong>Data Laboratorium (bila ada):</strong>
                    <textarea rows="3" readonly>{{ $data->data_laboratorium }}</textarea>
                    <p>Tgl Pemeriksaan: <input type="text" style="width: 50%;" value="{{ $data->tanggal_pemeriksaan_lab?->format('d-m-Y') }}" readonly></p>
                </div>
                <div class="signature-area">
                    <p>{{ $data->tanggal_laporan?->format('d-m-Y') ?? '........., tgl .................... 20....' }}</p>
                    <p>Tanda Tangan Pelapor</p>
                    <br><br>
                    <p>( {{ $data->nama_pelapor ?: '.......................................' }} )</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
