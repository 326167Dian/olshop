<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Konseling</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .border-box {
            border: 1px solid #000;
            padding: 20px;
        }

        .logo {
            max-height: 70px;
            display: block;
            margin: 0 auto 10px;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        table.form-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.form-table td {
            padding: 4px 2px;
            vertical-align: top;
        }

        .tall-row .content-area {
            min-height: 40px;
            border-bottom: 1px dotted #999;
            padding: 4px 0;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
            width: 220px;
        }

        .signature-box p {
            margin-bottom: 60px;
        }

        .dot-line {
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .alert-danger {
            border: 1px solid #f5c6cb;
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="border-box">
            @if ($setheader && $setheader->logo)
                <img src="{{ asset('storage/' . $setheader->logo) }}" alt="Logo" class="logo">
            @endif
            <h1>DOKUMENTASI KONSELING {{ $setheader->satu ?? '' }}</h1>

            <table class="form-table">
                <tr>
                    <td width="35%">Nama Pasien</td>
                    <td width="2%">:</td>
                    <td>{{ $konseling->nm_pelanggan }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $konseling->pelanggan->jenis_kelamin ?? '' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td>:</td>
                    <td>{{ $konseling->pelanggan->tanggal_lahir ? \Carbon\Carbon::parse($konseling->pelanggan->tanggal_lahir)->format('d-m-Y') : '' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $konseling->pelanggan->alamat_pelanggan ?? '' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Konseling</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($konseling->tgl_konseling)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td>Nama Dokter</td>
                    <td>:</td>
                    <td>{{ $konseling->nama_dokter }}</td>
                </tr>
                <tr>
                    <td>Diagnosa</td>
                    <td>:</td>
                    <td>{{ $konseling->diagnosa }}</td>
                </tr>
                <tr class="tall-row">
                    <td colspan="3">
                        Riwayat Penyakit / Pemberian obat sebelumnya :
                        <div class="content-area">{{ $konseling->riwayat_penyakit }}</div>
                    </td>
                </tr>
                <tr>
                    <td>Riwayat Alergi</td>
                    <td>:</td>
                    <td>{{ $konseling->riwayat_alergi }}</td>
                </tr>
                <tr class="tall-row">
                    <td colspan="3">
                        Keluhan :
                        <div class="content-area">{{ $konseling->keluhan }}</div>
                    </td>
                </tr>
                <tr>
                    <td>Kapan Pasien Terakhir Berkunjung ke Apotek</td>
                    <td>:</td>
                    <td>{{ $konseling->visite }}</td>
                </tr>
                <tr class="tall-row">
                    <td colspan="3">
                        Tindak Lanjut :
                        <div class="content-area">{{ $konseling->tindakan }}</div>
                    </td>
                </tr>
            </table>

            <div class="signature-section">
                <div class="signature-box">
                    <p>Apoteker</p>
                    <div class="dot-line">{{ $setheader->empat ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
