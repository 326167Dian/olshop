<!doctype html>
<html>
<head>
    <title>Laporan Data Penjualan</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h4>MySIFA LAPORAN PENJUALAN</h4></center>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Kode Barang</th>
                <th style="text-align:center">Nama Barang</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:center">Satuan</th>
                <th style="text-align:center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td style="width:150px">{{ $row->kd_barang }}</td>
                    <td style="width:300px">{{ $row->nmbrg_dtrkasir }}</td>
                    <td style="text-align:center; width:80px">{{ $row->qty_total }}</td>
                    <td style="text-align:center; width:100px">{{ $row->sat_dtrkasir }}</td>
                    <td style="text-align:right; width:100px">{{ number_format($row->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach ($breakdown as $b)
        <p style="font-weight:bold">Pembayaran {{ $b['nm_carabayar'] }} : Rp. {{ number_format($b['total'], 0, ',', '.') }}</p>
    @endforeach
    <p style="font-weight:bold; font-size:24px">
        GRAND TOTAL PENJUALAN SHIFT {{ $shiftLabel }} : Rp. {{ number_format($grandTotal, 0, ',', '.') }}
    </p>
</body>
</html>
