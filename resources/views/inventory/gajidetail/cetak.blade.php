<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip Gaji {{ $slip->admin->nama_lengkap ?? '' }} {{ $slip->periode_bulan }}</title>
    <style>
        @page {
            size: 210mm 148mm;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #28966e;
            padding-bottom: 6px;
        }

        .header .logo img {
            max-height: 55px;
        }

        .header .apotek-nama {
            font-size: 20px;
            font-weight: bold;
            color: #c81e2d;
        }

        .header .alamat {
            text-align: right;
            font-size: 10px;
        }

        h1.judul {
            text-align: center;
            color: #c81e2d;
            font-size: 18px;
            margin: 8px 0 0;
        }

        h2.subjudul {
            text-align: center;
            color: #28966e;
            font-size: 13px;
            margin: 2px 0 10px;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.info td {
            background: #e6f5eb;
            padding: 4px 8px;
        }

        table.info td.label {
            width: 18%;
        }

        table.info td.sep {
            width: 2%;
        }

        table.komponen {
            width: 100%;
            border-collapse: collapse;
        }

        table.komponen th {
            background: #c81e2d;
            color: #fff;
            padding: 5px;
            border: 1px solid #c81e2d;
        }

        table.komponen td {
            padding: 4px 6px;
            border: 1px solid #999;
        }

        table.komponen td.num {
            text-align: right;
        }

        tr.total-row td {
            background: #e6f5eb;
            font-weight: bold;
        }

        tr.potongan-row td {
            background: #fbebdc;
        }

        tr.potongan-row td.num {
            color: #c81e2d;
        }

        tr.take-home-row td {
            background: #fbe1e1;
            font-weight: bold;
            font-size: 13px;
        }

        tr.transfer-row td {
            font-size: 10px;
        }

        .signature {
            width: 55%;
            margin-left: 45%;
            text-align: center;
            margin-top: 14px;
        }

        .signature .nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 40px;
        }

        .btn-print {
            margin: 10px 0;
        }

        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            @if ($setheader && $setheader->logo_url)
                <img src="{{ $setheader->logo_url }}" alt="Logo">
            @endif
        </div>
        <div class="apotek-nama">{{ $setheader->satu ?? 'APOTEK' }}</div>
        <div class="alamat">
            <div>{{ $setheader->dua ?? '' }}</div>
            <div>{{ $setheader->tiga ?? '' }}</div>
            <div>{{ $setheader->enam ?? '' }}</div>
        </div>
    </div>

    <h1 class="judul">SLIP GAJI KARYAWAN</h1>
    <h2 class="subjudul">{{ $setheader->satu ?? 'APOTEK' }}</h2>

    <table class="info">
        <tr>
            <td class="label">Periode</td>
            <td class="sep">:</td>
            <td>{{ $slip->periode_text }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td class="sep">:</td>
            <td>{{ $slip->admin->nama_lengkap ?? '-' }}</td>
        </tr>
    </table>

    @php
        $totalPenghasilan = $slip->gaji_pokok + $slip->transportasi + $slip->lembur + $slip->komisi;
        $namaBank = $slip->admin->nama_bank ?: '-';
        $noRek = $slip->admin->rekening_bank ?: '-';
    @endphp

    <table class="komponen">
        <thead>
            <tr>
                <th style="width:6%">No.</th>
                <th style="width:60%">Komponen Penghasilan</th>
                <th style="width:34%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Gaji Pokok</td>
                <td class="num">{{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Transportasi / Extra fooding</td>
                <td class="num">{{ number_format($slip->transportasi, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Lembur</td>
                <td class="num">{{ number_format($slip->lembur, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Komisi</td>
                <td class="num">{{ number_format($slip->komisi, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">Total Penghasilan</td>
                <td class="num">{{ number_format($totalPenghasilan, 0, ',', '.') }}</td>
            </tr>
            <tr class="potongan-row">
                <td colspan="2">Potongan: Pinjaman / Kasbon</td>
                <td class="num">- {{ number_format($slip->pinjaman, 0, ',', '.') }}</td>
            </tr>
            <tr class="take-home-row">
                <td colspan="2">Penerimaan Bersih (Take Home Pay)</td>
                <td class="num">Rp {{ number_format($slip->total, 0, ',', '.') }}</td>
            </tr>
            <tr class="transfer-row">
                <td colspan="3">Transfer ke Bank/e-wallet {{ $namaBank }} no rek {{ $noRek }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
        <div>Mengetahui,</div>
        <div class="nama">{{ trim(str_ireplace('Apoteker :', '', $setheader->empat ?? '')) ?: 'Pimpinan Apotek' }}</div>
        <div>Pimpinan {{ $setheader->satu ?? 'Apotek' }}</div>
    </div>

    <div class="btn-print">
        <button onclick="window.print()">Cetak</button>
    </div>
</body>

</html>
