<!doctype html>
<html>
<head>
    <title>Laporan Data Barang</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h1>DATABASE BARANG APOTEK</h1></center>
    <p>Dicetak Oleh : {{ $admin->nama_lengkap }}&nbsp;&nbsp;Tanggal : {{ $tanggal }}</p>
    <table border="1">
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>No. Batch</th>
            <th>Stok Barang</th>
            <th>Satuan</th>
            <th>Jenis &amp; Rak</th>
            <th>Harga Beli</th>
            <th>Harga Jual Reguler</th>
            <th>Harga Jual Dokter</th>
            <th>Harga Jual Halodoc</th>
            <th>Harga Jual Market place</th>
        </tr>
        @foreach ($rows as $i => $barang)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ (int) $barang->kd_barang }}</td>
                <td>{{ $barang->nm_barang }}</td>
                <td style="text-align:center;">
                    @foreach (($batchPerBarang[$barang->kd_barang] ?? []) as $b)
                        {{ $b->no_batch }}<br>
                    @endforeach
                </td>
                <td style="text-align:center;">{{ $barang->stok_barang }}</td>
                <td style="text-align:center;">{{ $barang->sat_barang }}</td>
                <td style="text-align:center;">{{ $barang->jenisobat }}</td>
                <td>{{ $barang->hrgsat_barang }}</td>
                <td>{{ $barang->hrgjual_barang }}</td>
                <td>{{ $barang->hrgjual_barang1 }}</td>
                <td>{{ $barang->hrgjual_barang2 }}</td>
                <td>{{ $barang->hrgjual_barang3 }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
