<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat PTO</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }

        h1 {
            text-align: center;
            font-size: 15px;
            margin-bottom: 5px;
        }

        p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9px;
        }

        th {
            background: #dcdcdc;
            text-align: center;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <h1>LAPORAN RIWAYAT PTO</h1>
    <p>Nama Pelanggan: {{ $pelanggan->nm_pelanggan }}</p>
    <p>
        Filter Tanggal:
        @if ($tglAwal || $tglAkhir)
            {{ $tglAwal ?: '...' }} s/d {{ $tglAkhir ?: '...' }}
        @else
            Semua Tanggal
        @endif
    </p>
    <p>Dicetak: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal 1</th>
                <th>Tanggal 2</th>
                <th>Catatan</th>
                <th>Obat</th>
                <th>Masalah</th>
                <th>Tindak</th>
                <th>Tempat</th>
                <th>TTD</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($riwayat as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->tanggal_1?->format('d-m-Y') }}</td>
                    <td>{{ $row->tanggal_2?->format('d-m-Y') }}</td>
                    <td>1) {{ $row->catatan_1 }}<br>2) {{ $row->catatan_2 }}</td>
                    <td>1) {{ $row->obat_1 }}<br>2) {{ $row->obat_2 }}</td>
                    <td>1) {{ $row->masalah_1 }}<br>2) {{ $row->masalah_2 }}</td>
                    <td>1) {{ $row->tindak_1 }}<br>2) {{ $row->tindak_2 }}</td>
                    <td>{{ $row->tempat_ttd }}</td>
                    <td>{{ $row->tanggal_ttd?->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data PTO.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak</button>
    </div>
</body>

</html>
