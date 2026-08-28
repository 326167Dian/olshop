<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pemantauan Terapi Obat</title>
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
            background: #fff;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .form-number {
            font-size: 11px;
            margin: 0;
        }

        .patient-data table td {
            padding: 2px 4px;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.main-table th,
        table.main-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 11px;
        }

        table.main-table th {
            background: #f0f0f0;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .sub-row {
            padding: 2px 0;
        }

        .footer-sign {
            margin-top: 30px;
            text-align: right;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <p class="form-number">Formulir 9</p>
            <h1 style="font-size: 16px;">DOKUMENTASI PEMANTAUAN TERAPI OBAT</h1>
        </div>

        <div class="patient-data">
            <table>
                <tr>
                    <td>Nama Pasien</td>
                    <td>:</td>
                    <td>{{ $pto->nm_pelanggan }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $pto->jenis_kelamin }}</td>
                </tr>
                <tr>
                    <td>Umur</td>
                    <td>:</td>
                    <td>{{ $pto->umur }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pto->alamat_pelanggan }}</td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td>:</td>
                    <td>{{ $pto->tlp_pelanggan }}</td>
                </tr>
            </table>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 25%;">Catatan Pengobatan Pasien</th>
                    <th style="width: 20%;">Nama Obat, Dosis, Cara Pemberian</th>
                    <th style="width: 20%;">Identifikasi Masalah terkait Obat</th>
                    <th style="width: 20%;">Rekomendasi/ Tindak Lanjut</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= 2; $i++)
                    <tr>
                        <td class="center">{{ $i }}</td>
                        <td>{{ $pto->{'tanggal_' . $i}?->format('d-m-Y') }}</td>
                        <td>
                            @if (trim((string) $pto->{'catatan_' . $i}) !== '')
                                <div class="sub-row">{!! nl2br(e($pto->{'catatan_' . $i})) !!}</div>
                            @else
                                <div class="sub-row">Riwayat penyakit</div>
                                <div class="sub-row">Riwayat penggunaan obat</div>
                                <div class="sub-row">Riwayat alergi</div>
                            @endif
                        </td>
                        <td>{!! nl2br(e($pto->{'obat_' . $i})) !!}</td>
                        <td>{!! nl2br(e($pto->{'masalah_' . $i})) !!}</td>
                        <td>{!! nl2br(e($pto->{'tindak_' . $i})) !!}</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="footer-sign">
            <p>{{ $pto->tempat_ttd ?: '........................' }}, {{ $pto->tanggal_ttd?->format('d-m-Y') ?? '20....' }}</p>
            <br><br><br>
            <p>Apoteker</p>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()">Cetak</button>
        </div>
    </div>

</body>

</html>
