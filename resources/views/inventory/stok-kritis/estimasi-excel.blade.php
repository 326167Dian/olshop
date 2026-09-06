<!doctype html>
<html>
<head>
    <title>Laporan Stok Kritis</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h1>STOK KRITIS</h1></center>
    Dicetak Oleh : {{ $printedBy }} Tanggal : {{ now()->format('d-m-Y') }}
    <table border="1">
        <tr>
            <th>No</th>
            <th>Kategori</th>
            <th>Nama Barang</th>
            <th>Qty/Stok</th>
            <th>T30</th>
            <th>Q30</th>
            <th>SFC max30</th>
            <th>SFCmax/week</th>
            <th>Satuan</th>
        </tr>
        @foreach ($rows as $i => $r)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td style="text-align:center">{{ $r->kategori_label }}</td>
                <td>{{ $r->nm_barang }}</td>
                <td style="text-align:center">{{ $r->stok_barang }}</td>
                <td style="text-align:center">{{ $r->t30 }}</td>
                <td style="text-align:center">{{ $r->q30 }}</td>
                <td style="text-align:center">{{ $r->sfc_max30 }}</td>
                <td style="text-align:center">{{ $r->sfc_max_week }}</td>
                <td style="text-align:center">{{ $r->sat_barang }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
