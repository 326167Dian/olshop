<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - {{ $order->kd_trbmasuk }}</title>
    <style>
        @page {
            size: {{ $layout === 'formal' ? '210mm 297mm' : '148mm 210mm' }};
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            background: #f0f0f0;
            color: #000;
        }

        .page {
            max-width: {{ $layout === 'formal' ? '900px' : '560px' }};
            margin: 0 auto;
            background: #fff;
            padding: 15px 20px;
        }

        .header-row {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header-row img {
            max-height: 60px;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text .nama-apotek {
            font-size: 18px;
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 8px 0;
        }

        .meta-row {
            display: flex;
            margin-bottom: 3px;
        }

        .meta-label {
            width: 110px;
        }

        .meta-colon {
            width: 12px;
        }

        table.item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.item-table th,
        table.item-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
            vertical-align: top;
        }

        table.item-table th {
            background: #f0f0f0;
            text-align: center;
        }

        .signature-block {
            margin-top: 25px;
            text-align: right;
        }

        .signature-block .ttd-img {
            max-height: 60px;
            margin: 5px 0;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .declare-block p {
            margin: 3px 0;
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
    <div class="page">
        <div class="header-row">
            @if ($setheader && $setheader->logo_url)
                <img src="{{ $setheader->logo_url }}" alt="Logo">
            @endif
            <div class="header-text">
                <div class="nama-apotek">{{ $setheader->satu ?? '' }}</div>
                @if ($layout === 'formal')
                    <div>No. SIPA : {{ $setheader->tujuh ?? '' }}</div>
                    <div>{{ $setheader->dua ?? '' }} {{ $setheader->tiga ?? '' }}</div>
                    <div>SIA : {{ $setheader->lima ?? '' }} Telp : {{ $setheader->enam ?? '' }}</div>
                @else
                    <div>{{ $setheader->dua ?? '' }}</div>
                    <div>{{ $setheader->tiga ?? '' }}</div>
                    <div>SIA : {{ $setheader->lima ?? '' }} Telp : {{ $setheader->enam ?? '' }}</div>
                    @if ($jenis === 'alkes')
                        <div>APJ : {{ $setheader->empat ?? '' }}</div>
                    @endif
                @endif
            </div>
        </div>

        <p class="title">{{ $title }}</p>

        @if ($layout === 'simple')
            <div class="meta-row"><div class="meta-label">Nomor SP</div><div class="meta-colon">:</div><div>{{ $noSp }}</div></div>
            <div class="meta-row"><div class="meta-label">Tanggal</div><div class="meta-colon">:</div><div>{{ $order->tgl_trbmasuk?->translatedFormat('d F Y') }}</div></div>
            <div class="meta-row"><div class="meta-label">Kepada</div><div class="meta-colon">:</div><div>{{ $order->nm_supplier }}</div></div>
            <div class="meta-row"><div class="meta-label">Alamat</div><div class="meta-colon">:</div><div>{{ $supplier->alamat_supplier ?? '' }}</div></div>

            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width:25px;">No.</th>
                        <th>Nama {{ $jenis === 'alkes' ? 'Alat Kesehatan' : 'Obat' }}</th>
                        <th style="width:60px;">Satuan</th>
                        <th style="width:55px;">Jumlah</th>
                        <th style="width:110px;">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->detail as $index => $row)
                        <tr>
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                            <td style="text-align:center;">{{ $row->satuan_tampil }}</td>
                            <td style="text-align:center;">{{ $row->qty_tampil }}</td>
                            <td style="text-align:center;">{{ $row->terbilang }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="signature-block">
                <p>{{ $setheader->tigabelas ?? '' }}, {{ now()->translatedFormat('d F Y') }}</p>
                <p>Apoteker Pemesan,</p>
                @if ($order->tandatangan === 'YA' && $setheader && $setheader->tandatangan_url)
                    <img class="ttd-img" src="{{ $setheader->tandatangan_url }}" alt="Tanda Tangan">
                @else
                    <br><br><br>
                @endif
                <p class="signature-name">{{ $setheader->empat ?? '' }}</p>
                <p style="font-size:9px;">{{ $setheader->tujuh ?? '' }}</p>
            </div>
        @else
            <div class="meta-row"><div>Nomor SP : {{ $noSp }}</div></div>

            <div class="declare-block" style="margin-top:10px;">
                <p>Yang bertandatangan di bawah ini :</p>
                <div class="meta-row"><div class="meta-label">Nama</div><div class="meta-colon">:</div><div>{{ $setheader->empat ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">Jabatan</div><div class="meta-colon">:</div><div>Apoteker Penanggung Jawab</div></div>
                <div class="meta-row"><div class="meta-label">No. SIPA</div><div class="meta-colon">:</div><div>{{ $setheader->tujuh ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">Alamat</div><div class="meta-colon">:</div><div>{{ $setheader->duabelas ?? '' }}</div></div>

                <p>Mengajukan pesanan Obat Jadi {{ $jenis === 'prekursor' ? 'Prekursor Farmasi' : 'OOT' }} kepada :</p>
                <div class="meta-row"><div class="meta-label">Nama Perusahaan</div><div class="meta-colon">:</div><div>{{ $order->nm_supplier }}</div></div>
                <div class="meta-row"><div class="meta-label">Alamat</div><div class="meta-colon">:</div><div>{{ $supplier->alamat_supplier ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">No Telp</div><div class="meta-colon">:</div><div>{{ $supplier->tlp_supplier ?? '' }}</div></div>
            </div>

            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width:25px;">No</th>
                        <th>Nama Obat Mengandung {{ $jenis === 'prekursor' ? 'Prekursor Farmasi' : 'OOT' }}</th>
                        <th style="width:120px;">Zat Aktif / Isi Kemasan</th>
                        <th style="width:90px;">Bentuk dan Kekuatan</th>
                        <th style="width:55px;">Satuan</th>
                        <th style="width:50px;">Jumlah</th>
                        <th style="width:90px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->detail as $index => $row)
                        <tr>
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                            <td>{{ $row->barang->ket_barang ?? '' }}</td>
                            <td>{{ $row->barang->dosis ?? '' }}</td>
                            <td style="text-align:center;">{{ $row->satuan_tampil }}</td>
                            <td style="text-align:center;">{{ $row->qty_tampil }}</td>
                            <td style="text-align:center;">{{ $row->terbilang }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="declare-block" style="margin-top:10px;">
                <p>Obat Jadi {{ $jenis === 'prekursor' ? 'Prekursor Farmasi' : 'OOT' }} tersebut akan digunakan untuk :</p>
                <div class="meta-row"><div class="meta-label">Nama</div><div class="meta-colon">:</div><div>{{ $setheader->satu ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">Alamat</div><div class="meta-colon">:</div><div>{{ $setheader->dua ?? '' }} {{ $setheader->tiga ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">Telp</div><div class="meta-colon">:</div><div>{{ $setheader->enam ?? '' }}</div></div>
                <div class="meta-row"><div class="meta-label">Surat Izin</div><div class="meta-colon">:</div><div>{{ $setheader->lima ?? '' }}</div></div>
            </div>

            <div class="signature-block">
                <p>{{ $setheader->tigabelas ?? '' }}, {{ $order->tgl_trbmasuk?->translatedFormat('d F Y') }}</p>
                <p>Apoteker Pemesan,</p>
                @if ($order->tandatangan === 'YA' && $setheader && $setheader->tandatangan_url)
                    <img class="ttd-img" src="{{ $setheader->tandatangan_url }}" alt="Tanda Tangan">
                @else
                    <br><br><br>
                @endif
                <p class="signature-name">{{ $setheader->empat ?? '' }}</p>
                <p style="font-size:9px;">{{ $setheader->tujuh ?? '' }}</p>
            </div>
        @endif

        <div class="no-print" style="text-align:center; margin-top:15px;">
            <button onclick="window.print()">Cetak</button>
        </div>
    </div>
</body>

</html>
