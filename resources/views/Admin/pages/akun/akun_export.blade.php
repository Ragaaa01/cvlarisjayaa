<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Akun</title>
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
        .email-column {
            width: 30%;
        }
        .nama-orang-column {
            width: 30%;
        }
        .role-column {
            width: 20%;
            text-align: center;
        }
        .status-aktif-column {
            width: 10%;
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
        <h1>Data Akun</h1>
        <div class="subtitle">Laporan Data Akun - {{ date('d M Y') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th class="no-column">No</th>
                <th class="email-column">Email</th>
                <th class="nama-orang-column">Nama Orang</th>
                <th class="role-column">Role</th>
                <th class="status-aktif-column">Status Aktif</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($akuns as $index => $akun)
                <tr>
                    <td class="no-column">{{ $index + 1 }}</td>
                    <td class="email-column">{{ $akun->email ?? '-' }}</td>
                    <td class="nama-orang-column">{{ $akun->orang->nama_lengkap }}</td>
                    <td class="role-column">{{ $akun->role->nama_role }}</td>
                    <td class="status-aktif-column">{{ $akun->status_aktif ? 'Aktif' : 'Non-Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data akun.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <div class="date">Dicetak pada: {{ date('d-m-Y H:i:s') }}</div>
        <div class="page-number"></div>
    </div>
</body>
</html>