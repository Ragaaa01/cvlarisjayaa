<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Orang Mitra</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333333;
            line-height: 1.4;
            margin: 0;
        }
        .header {
            position: fixed;
            top: -10mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            border-bottom: 1px solid #d1d1d1;
            padding-bottom: 5mm;
        }
        .header h1 {
            font-size: 14pt;
            color: #014A7F;
            margin: 0;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 8pt;
            color: #666666;
            margin-top: 2mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20mm;
            font-size: 9pt;
        }
        th, td {
            border: 0.5pt solid #999999;
            padding: 3mm 4mm;
            vertical-align: top;
            text-align: left;
        }
        th {
            background-color: #014A7F;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        td {
            background-color: #ffffff;
        }
        .no-column {
            width: 10%;
            text-align: center;
        }
        .nama-orang-column {
            width: 35%;
        }
        .nama-mitra-column {
            width: 35%;
        }
        .status-valid-column {
            width: 20%;
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #666666;
            border-top: 1px solid #d1d1d1;
            padding-top: 3mm;
        }
        .footer .date {
            margin-bottom: 2mm;
        }
        .footer .page-number:after {
            content: "Halaman " counter(page) " dari " counter(pages);
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        tr:hover td {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Orang Mitra</h1>
        <div class="subtitle">Laporan Data Orang Mitra - {{ date('d M Y') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th class="no-column">No</th>
                <th class="nama-orang-column">Nama Orang</th>
                <th class="nama-mitra-column">Nama Mitra</th>
                <th class="status-valid-column">Status Valid</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orangMitras as $index => $orangMitra)
                <tr>
                    <td class="no-column">{{ $index + 1 }}</td>
                    <td class="nama-orang-column">{{ $orangMitra->orang->nama_lengkap }}</td>
                    <td class="nama-mitra-column">{{ $orangMitra->mitra->nama_mitra }}</td>
                    <td class="status-valid-column">{{ $orangMitra->status_valid ? 'Valid' : 'Tidak Valid' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <div class="date">Dicetak pada: {{ date('d-m-Y H:i:s') }}</div>
        <div class="page-number"></div>
    </div>
</body>
</html>