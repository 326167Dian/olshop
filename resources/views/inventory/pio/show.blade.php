<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir 8. Dokumentasi PIO</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            padding: 20px;
            background: #f0f0f0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .main-border {
            border: 1px solid #000;
            background: #fff;
            padding: 15px;
        }

        .main-border h3 {
            text-align: center;
            font-size: 15px;
            margin-top: 0;
        }

        .row.section-top {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .col {
            margin-bottom: 3px;
        }

        .flex-row {
            display: flex;
            gap: 15px;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0 5px;
        }

        .content-row {
            margin-bottom: 4px;
        }

        .input-line {
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
        }

        .input-area {
            width: 100%;
            border: 1px solid #000;
            font-family: inherit;
            margin-bottom: 8px;
        }

        table.question-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.question-grid td {
            border: 1px solid #000;
            padding: 3px 5px;
        }

        .checkbox-cell {
            width: 20px;
            text-align: center;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }

        .footer-section {
            margin-top: 10px;
        }

        .logo {
            max-height: 60px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-label">Formulir 8. Dokumentasi PIO</div>

        <div class="main-border">
            <h3>DOKUMENTASI PELAYANAN INFORMASI OBAT (PIO) {{ $setheader->satu ?? '' }}</h3>

            <div class="row section-top">
                <div style="flex: 1;">
                    <div class="col">No. <input type="text" class="input-line" style="width: 80px;" value="{{ $pio->no_pio }}" readonly></div>
                    <div class="col">Tanggal: <input type="text" class="input-line" value="{{ $pio->tanggal?->format('d-m-Y') }}" readonly></div>
                    <div class="col">Waktu: <input type="text" class="input-line" value="{{ $pio->waktu }}" readonly></div>
                    <div class="col">Metode: <input type="text" class="input-line" value="{{ $pio->metode }}" readonly></div>
                </div>
                @if ($setheader && $setheader->logo_url)
                    <div><img src="{{ $setheader->logo_url }}" alt="Logo" class="logo"></div>
                @endif
            </div>

            <div class="section-title">Identitas Penanya</div>
            <div class="content-row flex-row">
                <div style="flex: 2;">Nama: <input type="text" class="input-line" style="width: 80%;" value="{{ $pio->nama_penanya }}" readonly></div>
                <div style="flex: 1;">No. Telp. <input type="text" class="input-line" style="width: 60%;" value="{{ $pio->no_telp_penanya }}" readonly></div>
            </div>
            <div class="content-row">
                Status :
                <label><input type="checkbox" {{ $pio->status_penanya == 'Pasien' ? 'checked' : '' }} disabled> Pasien</label> /
                <label><input type="checkbox" {{ $pio->status_penanya == 'Keluarga Pasien' ? 'checked' : '' }} disabled> Keluarga Pasien</label> /
                <label><input type="checkbox" {{ $pio->status_penanya == 'Petugas Kesehatan' ? 'checked' : '' }} disabled> Petugas Kesehatan</label>
                (<input type="text" class="input-line" style="width: 40%;" value="{{ $pio->status_penanya_ket }}" readonly>)
            </div>

            <div class="section-title">Data Pasien</div>
            <div class="content-row">
                Nama: <input type="text" class="input-line" style="width: 200px;" value="{{ $pio->pelanggan->nm_pelanggan ?? '' }}" readonly>
            </div>
            <div class="content-row">
                Umur: <input type="text" class="input-line" style="width: 40px;" value="{{ $pio->umur_pasien }}" readonly> tahun;
                Tinggi: <input type="text" class="input-line" style="width: 40px;" value="{{ $pio->tinggi_pasien }}" readonly> cm;
                Berat: <input type="text" class="input-line" style="width: 40px;" value="{{ $pio->berat_pasien }}" readonly> kg;
            </div>
            <div class="content-row">
                Jenis kelamin:
                <label><input type="checkbox" {{ $pio->jenis_kelamin == 'L' ? 'checked' : '' }} disabled> Laki-laki</label> /
                <label><input type="checkbox" {{ $pio->jenis_kelamin == 'P' ? 'checked' : '' }} disabled> Perempuan</label>
            </div>
            <div class="content-row flex-row">
                <div style="flex: 1;">
                    Kehamilan: <label><input type="checkbox" {{ $pio->kehamilan ? 'checked' : '' }} disabled> Ya</label>
                    (<input type="text" class="input-line" style="width: 30px;" value="{{ $pio->kehamilan_minggu }}" readonly> minggu) /
                    <label><input type="checkbox" {{ !$pio->kehamilan ? 'checked' : '' }} disabled> Tidak</label>
                </div>
                <div style="flex: 1;">
                    Menyusui: <label><input type="checkbox" {{ $pio->menyusui ? 'checked' : '' }} disabled> Ya</label> /
                    <label><input type="checkbox" {{ !$pio->menyusui ? 'checked' : '' }} disabled> Tidak</label>
                </div>
            </div>

            <div class="section-title">Pertanyaan</div>
            <div class="content-row">Uraian Pertanyaan:</div>
            <textarea class="input-area" rows="3" readonly>{{ $pio->uraian_pertanyaan }}</textarea>

            <div class="content-row" style="margin-top: 5px;">Jenis Pertanyaan:</div>
            <table class="question-grid">
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_identifikasi_obat ? 'checked' : '' }} disabled></td><td>Identifikasi Obat</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_stabilitas ? 'checked' : '' }} disabled></td><td>Stabilitas</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_farmakokinetika ? 'checked' : '' }} disabled></td><td>Farmakokinetika</td>
                </tr>
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_interaksi_obat ? 'checked' : '' }} disabled></td><td>Interaksi Obat</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_dosis ? 'checked' : '' }} disabled></td><td>Dosis</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_farmakodinamika ? 'checked' : '' }} disabled></td><td>Farmakodinamika</td>
                </tr>
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_harga_obat ? 'checked' : '' }} disabled></td><td>Harga Obat</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_keracunan ? 'checked' : '' }} disabled></td><td>Keracunan</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_ketersediaan_obat ? 'checked' : '' }} disabled></td><td>Ketersediaan Obat</td>
                </tr>
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_kontra_indikasi ? 'checked' : '' }} disabled></td><td>Kontra Indikasi</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_efek_samping ? 'checked' : '' }} disabled></td><td>Efek Samping Obat</td>
                    <td class="checkbox-cell" rowspan="2" style="border-right:none;"><input type="checkbox" {{ $pio->jenis_pertanyaan_lain_lain ? 'checked' : '' }} disabled></td>
                    <td rowspan="2" style="border-left:none;">Lain-lain<br><input type="text" class="input-line" style="width: 90%;" value="{{ $pio->jenis_pertanyaan_lain_lain_ket }}" readonly></td>
                </tr>
                <tr>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_cara_pemakaian ? 'checked' : '' }} disabled></td><td>Cara Pemakaian</td>
                    <td class="checkbox-cell"><input type="checkbox" {{ $pio->jenis_pertanyaan_penggunaan_terapeutik ? 'checked' : '' }} disabled></td><td>Penggunaan Terapeutik</td>
                </tr>
            </table>

            <div class="section-title">Jawaban</div>
            <textarea class="input-area" rows="4" readonly>{{ $pio->jawaban }}</textarea>

            <div class="section-title">Referensi</div>
            <textarea class="input-area" rows="2" readonly>{{ $pio->referensi }}</textarea>

            <div class="content-row border-bottom">
                Penyampaian Jawaban:
                <label><input type="checkbox" {{ $pio->penyampaian_jawaban == 'Segera' ? 'checked' : '' }} disabled> Segera</label> /
                <label><input type="checkbox" {{ $pio->penyampaian_jawaban == 'Dalam 24 jam' ? 'checked' : '' }} disabled> Dalam 24 jam</label> /
                <label><input type="checkbox" {{ $pio->penyampaian_jawaban == 'Lebih dari 24 jam' ? 'checked' : '' }} disabled> Lebih dari 24 jam</label>
            </div>

            <div class="footer-section">
                <div class="content-row">Apoteker yang menjawab:</div>
                <input type="text" class="input-line" style="width: 50%; margin-left: 10px; font-weight: bold;" value="{{ $pio->apoteker_penjawab }}" readonly>
                <div class="content-row flex-row" style="margin-top: 10px;">
                    <div style="flex: 1;">Tanggal: <input type="text" class="input-line" value="{{ $pio->tanggal_jawab?->format('d-m-Y') }}" readonly></div>
                    <div style="flex: 1;">Waktu: <input type="text" class="input-line" value="{{ $pio->waktu_jawab }}" readonly></div>
                </div>
                <div class="content-row">
                    Metode Jawaban :
                    <label><input type="checkbox" {{ $pio->metode_jawab == 'Lisan' ? 'checked' : '' }} disabled> Lisan</label> /
                    <label><input type="checkbox" {{ $pio->metode_jawab == 'Tertulis' ? 'checked' : '' }} disabled> Tertulis</label> /
                    <label><input type="checkbox" {{ $pio->metode_jawab == 'Telepon' ? 'checked' : '' }} disabled> Telepon</label>
                </div>
            </div>
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Formulir</button>
        </div>
    </div>

</body>

</html>
