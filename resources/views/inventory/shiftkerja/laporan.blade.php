<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Shift</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            width: 74mm;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .title {
            font-weight: bold;
            font-size: 13px;
        }

        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        table.info {
            width: 100%;
        }

        table.info td {
            padding: 1px 0;
            vertical-align: top;
        }

        table.info td.label {
            width: 40%;
        }

        table.info td.val {
            text-align: right;
        }

        .btn-print {
            margin: 8px 0;
        }

        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="center">
        @if ($setheader)
            <div class="title">{{ $setheader->satu }}</div>
            <div>{{ $setheader->dua }}</div>
            <div>{{ $setheader->tiga }}</div>
            <div>{{ $setheader->empat }}</div>
            <div>{{ $setheader->lima }}</div>
            <div>{{ $setheader->enam }}</div>
        @endif
    </div>

    <hr>
    <div class="center title">LAPORAN SHIFT</div>
    <hr>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td>
            <td class="val">{{ \Illuminate\Support\Carbon::parse($waktuKerja->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Total Penjualan</td>
            <td class="val">{{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        </tr>
        @foreach ($perCaraBayar as $cb)
            <tr>
                <td class="label">{{ $cb->nm_carabayar }}</td>
                <td class="val">{{ number_format($cb->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="label">Jumlah Transaksi</td>
            <td class="val">{{ $jumlahTransaksi }}</td>
        </tr>
        <tr>
            <td class="label">Petugas Buka</td>
            <td class="val">{{ $jumlahTransaksi == 0 ? '' : $waktuKerja->petugasbuka }}</td>
        </tr>
        <tr>
            <td class="label">Petugas Tutup</td>
            <td class="val">{{ $jumlahTransaksi == 0 ? '' : $waktuKerja->petugastutup }}</td>
        </tr>
    </table>

    <hr>
    <div class="center">
        @if ($setheader)
            <div>{{ $setheader->delapan }}</div>
            <div>{{ $setheader->sembilan }}</div>
            <div>{{ $setheader->sepuluh }}</div>
        @endif
    </div>

    <div class="center btn-print">
        <button onclick="window.print()">Cetak</button>
    </div>
</body>

</html>
