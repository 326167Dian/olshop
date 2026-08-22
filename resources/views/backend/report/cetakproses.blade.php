<style>
    table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #ccc;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    table th,
    table td {
        border: 1px solid #ccc;
        padding: 6px;
        text-align: left;
    }

    table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
</style>

<table>
    <tr>
        <td>
            <strong>Perihal:</strong> {{ $judul }} <br>
            <strong>Laporan:</strong> {{ $subJudul }} <br>
            <strong>Tanggal:</strong> {{ $tanggalAwal }} s.d {{ $tanggalAkhir }}
        </td>
    </tr>
</table>

<p></p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>ID Pesanan</th>
            <th>Nama Pemesan</th>
            <th>Tanggal Pesanan</th>
            <th>Harga Pesanan</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cetak as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->kode_pesanan }}</td>
            <td>{{ $row->user->name }}</td>
            <td>{{ $row->created_at->format('d M Y H:i') }}</td>
            <td>Rp. {{ number_format($row->total_harga + $row->biaya_ongkir, 0, ',', '.') }}</td>
            <td>{{ $row->user->email }}</td>
        </tr>
        @endforeach
    </tbody>
</table>