<!doctype html>
<html>
<head>
    <title>Laporan Stok Opname</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h1>LAPORAN STOK OPNAME</h1></center>
    Dicetak Oleh : {{ $printedBy }} Tanggal : {{ now()->format('Y-m-d') }}
    <table border="1">
        <tr style="text-align:center; font-weight:bold;">
            <td>No</td>
            <td>Petugas</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Satuan</td>
            <td>Expire Date</td>
            <td>Jumlah ED</td>
            <td>Stok Komputer</td>
            <td>Stok Fisik</td>
            <td>Selisih</td>
            <td>Waktu</td>
            <td>Harga</td>
            <td>Total</td>
        </tr>
        @foreach ($rows as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->nama_lengkap }}</td>
                <td>{{ $r->kd_barang }}</td>
                <td>{{ $r->nm_barang }}</td>
                <td style="text-align:center">{{ $r->sat_barang }}</td>
                <td style="text-align:center">{{ $r->exp_date }}</td>
                <td style="text-align:center">{{ $r->jml }}</td>
                <td style="text-align:center">{{ $r->stok_sistem }}</td>
                <td style="text-align:center">{{ $r->stok_fisik }}</td>
                <td style="text-align:center">{{ $r->selisih }}</td>
                <td>{{ $r->tgl_current }}</td>
                <td>{{ $r->hrgsat_barang }}</td>
                <td>{{ $r->ttl_hrgbrg }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
