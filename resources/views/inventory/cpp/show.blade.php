<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Catatan Pengobatan Pasien</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            background: #f0f0f0;
            padding: 20px;
        }

        .page-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .main-title {
            text-align: center;
            font-size: 16px;
            margin: 0;
        }

        .logo {
            max-height: 60px;
        }

        .identity-section {
            margin-bottom: 15px;
        }

        .info-group {
            display: flex;
            margin-bottom: 4px;
        }

        .label {
            width: 130px;
        }

        .colon {
            width: 15px;
        }

        .fill-input {
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
            width: 300px;
        }

        table.medication-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.medication-table th,
        table.medication-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 11px;
        }

        table.medication-table th {
            background: #f0f0f0;
        }

        .table-input {
            border: none;
            background: transparent;
            width: 100%;
            font-family: inherit;
        }

        .table-area {
            border: none;
            background: transparent;
            width: 100%;
            font-family: inherit;
            resize: none;
        }

        .signature-section {
            margin-top: 30px;
            text-align: right;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            margin-top: 50px;
        }

        .input-line {
            border: none;
            border-bottom: 1px dotted #000;
            background: transparent;
            font-family: inherit;
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
    <div class="page-container">
        <div class="header-section">
            <div class="title-container">
                <h2 class="main-title">CATATAN PENGOBATAN PASIEN <br>{{ $setheader->satu ?? '' }}</h2>
            </div>
            @if ($setheader && $setheader->logo_url)
                <img src="{{ $setheader->logo_url }}" alt="Logo" class="logo">
            @endif
        </div>

        <div class="identity-section">
            <div class="info-group">
                <div class="label">Nama Pasien</div>
                <div class="colon">:</div>
                <div><input type="text" class="fill-input" value="{{ $cpp->nama_pasien }}" readonly></div>
            </div>
            <div class="info-group">
                <div class="label">Jenis Kelamin</div>
                <div class="colon">:</div>
                <div>
                    <input type="text" class="fill-input" style="width: 150px;" value="{{ $cpp->jk }}" readonly>
                    <span style="margin-left: 60px;">Umur : </span>
                    <input type="text" class="fill-input" style="width: 80px;" value="{{ $cpp->umur }}" readonly>
                </div>
            </div>
            <div class="info-group">
                <div class="label">Alamat</div>
                <div class="colon">:</div>
                <div><input type="text" class="fill-input" value="{{ $cpp->alamat }}" readonly></div>
            </div>
            <div class="info-group">
                <div class="label">No. Telepon</div>
                <div class="colon">:</div>
                <div><input type="text" class="fill-input" value="{{ $cpp->telp }}" readonly></div>
            </div>
        </div>

        <table class="medication-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No.</th>
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 150px;">Nama Dokter</th>
                    <th>Nama Obat/ Dosis/ Cara Pemberian</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cpp->detail as $idx => $detail)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td><input type="text" class="table-input" value="{{ $detail->tanggal }}" readonly></td>
                        <td><input type="text" class="table-input" value="{{ $detail->nama_dokter }}" readonly></td>
                        <td>
                            <textarea class="table-area" rows="3" readonly>{{ $detail->nama_obat_dosis }}</textarea>
                            @if ($detail->catatan)
                                <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #ddd;">
                                    <strong style="font-size: 11px;">Catatan:</strong>
                                    <textarea class="table-area" rows="2" readonly>{{ $detail->catatan }}</textarea>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    @for ($i = 1; $i <= 3; $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i }}</td>
                            <td><input type="text" class="table-input" readonly></td>
                            <td><input type="text" class="table-input" readonly></td>
                            <td><textarea class="table-area" rows="3" readonly></textarea></td>
                        </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        <div class="signature-section">
            <p>Bekasi, <input type="text" class="input-line" style="width: 150px;" value="{{ $cpp->tgl_ttd }}" readonly> 20<input type="text" class="input-line" style="width: 30px;" value="{{ $cpp->thn_ttd }}" readonly></p>
            <div class="signature-box">
                <input type="text" class="fill-input" style="text-align: center; font-weight: bold; text-decoration: underline;" value="{{ $cpp->nama_apoteker }}" readonly>
                <p>SIPA. <input type="text" class="fill-input" style="text-align: center; width: 200px;" value="{{ $cpp->sipa_apoteker }}" readonly></p>
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak Manual</button>
    </div>

</body>

</html>
