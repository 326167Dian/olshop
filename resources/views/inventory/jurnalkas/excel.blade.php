<!doctype html>
<html>
<head>
    <title>Jurnal Kas</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h1>JURNAL KAS</h1></center>
    Dicetak Oleh : {{ $printedBy }} Tanggal : {{ now()->format('d-m-Y') }}
    <table border="1">
        <tr>
            <th>No</th>
            <th>Id Jurnal</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Petugas</th>
            <th>Jenis Transaksi</th>
            <th>Debit</th>
            <th>Kredit</th>
            <th>Cara Bayar</th>
            <th>Waktu Input</th>
        </tr>
        @foreach ($rows as $i => $r)
            <tr>
                <td style="text-align:center">{{ $i + 1 }}</td>
                <td>{{ $r->id_jurnal }}</td>
                <td>{{ $r->tanggal->format('Y-m-d') }}</td>
                <td style="text-align:center">{{ $r->ket }}</td>
                <td style="text-align:center">{{ $r->petugas }}</td>
                <td style="text-align:right">{{ optional($r->jenis)->nm_jurnal }}</td>
                <td style="text-align:right">{{ number_format((float) $r->debit, 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format((float) $r->kredit, 0, ',', '.') }}</td>
                <td style="text-align:center">{{ $r->carabayar }}</td>
                <td style="text-align:center">{{ $r->current }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
