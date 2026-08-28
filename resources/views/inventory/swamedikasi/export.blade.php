<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Swamedikasi Pelanggan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 20px;
            background: #f0f0f0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
        }

        .header p {
            margin: 2px 0;
        }

        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 10px 0;
        }

        .patient-data table td {
            padding: 2px 6px;
            vertical-align: top;
        }

        table.riwayat-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.riwayat-table th,
        table.riwayat-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        table.riwayat-table th {
            background: #dcdcdc;
            text-align: center;
        }

        .footer-print {
            text-align: right;
            font-style: italic;
            margin-top: 15px;
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
            <h2>{{ $setheader->satu ?? '' }}</h2>
            <p>{{ $setheader->dua ?? '' }}</p>
            <p>{{ $setheader->tiga ?? '' }}</p>
            <p>{{ $setheader->empat ?? '' }}</p>
        </div>

        <p class="title">RIWAYAT SWAMEDIKASI PELANGGAN</p>

        <div class="patient-data">
            <table>
                <tr>
                    <td style="width:120px;">Nama</td>
                    <td style="width:15px;">:</td>
                    <td>{{ $pelanggan->nm_pelanggan }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $pelanggan->jenis_kelamin }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td>:</td>
                    <td>{{ $pelanggan->tanggal_lahir ? \Carbon\Carbon::parse($pelanggan->tanggal_lahir)->format('d-m-Y') : '' }}</td>
                </tr>
                <tr>
                    <td>Telepon</td>
                    <td>:</td>
                    <td>{{ $pelanggan->tlp_pelanggan }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pelanggan->alamat_pelanggan }}</td>
                </tr>
            </table>
        </div>

        <table class="riwayat-table">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:80px;">Tanggal</th>
                    <th>Diagnosa</th>
                    <th>Tindakan</th>
                    <th>Followup</th>
                    <th style="width:80px;">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $index => $row)
                    <tr>
                        <td style="text-align:center;">{{ $index + 1 }}</td>
                        <td>{{ $row->tgl?->format('d-m-Y') }}</td>
                        <td>{{ $row->diagnosa }}</td>
                        <td>
                            @if ($row->obat->isNotEmpty())
                                @foreach ($row->obat as $ob)
                                    {{ $ob->nm_barang }}@if ($ob->aturan_pakai) - {{ $ob->aturan_pakai }} @endif<br>
                                @endforeach
                            @else
                                {{ $row->tindakan }}
                            @endif
                        </td>
                        <td>{{ $row->followup }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">Belum ada riwayat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="footer-print">Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak</button>
    </div>

</body>

</html>
