<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Pharmacy Care</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            background: #f0f0f0;
            padding: 20px;
        }

        .page-container {
            width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm 15mm;
        }

        .main-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .sub-title {
            text-align: center;
            font-size: 14px;
            margin: 0 0 20px 0;
        }

        .id-grid {
            margin-bottom: 20px;
        }

        .id-row {
            display: flex;
            margin-bottom: 4px;
        }

        .id-label {
            width: 140px;
        }

        .id-colon {
            width: 15px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        table.data-table th {
            text-align: center;
            background: #f0f0f0;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page-container {
                width: auto;
                padding: 10mm;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <p class="main-title">HOME PHARMACY CARE</p>
        <p class="sub-title">{{ $setheader->satu ?? '' }}</p>

        <div class="id-grid">
            <div class="id-row">
                <div class="id-label">No. Home Care</div>
                <div class="id-colon">:</div>
                <div>{{ $homecare->no_homecare }}</div>
            </div>
            <div class="id-row">
                <div class="id-label">Nama Pasien</div>
                <div class="id-colon">:</div>
                <div>{{ $homecare->nama_pasien }}</div>
            </div>
            <div class="id-row">
                <div class="id-label">Umur</div>
                <div class="id-colon">:</div>
                <div>{{ $homecare->umur }}</div>
            </div>
            <div class="id-row">
                <div class="id-label">Alamat</div>
                <div class="id-colon">:</div>
                <div>{{ $homecare->alamat }}</div>
            </div>
            <div class="id-row">
                <div class="id-label">No. Telepon</div>
                <div class="id-colon">:</div>
                <div>{{ $homecare->telp }}</div>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No.</th>
                    <th style="width: 130px;">Tgl. Kunjungan</th>
                    <th>Catatan Apoteker</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($homecare->detail as $idx => $detail)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ $detail->tgl_kunjungan }}</td>
                        <td>{{ $detail->catatan_apoteker }}</td>
                    </tr>
                @empty
                    @for ($i = 1; $i <= 3; $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak</button>
    </div>

</body>

</html>
