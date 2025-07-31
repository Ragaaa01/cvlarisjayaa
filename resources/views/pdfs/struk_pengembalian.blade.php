<!DOCTYPE html>
<html>
<head>
    <title>Bukti Pengembalian Tabung</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .container { border: 1px solid #eee; padding: 20px; width: 100%; }
        h2 { text-align: center; margin-top: 0; margin-bottom: 20px; color: #001848; }
        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-table td:first-child { width: 150px; font-weight: bold; }
        .summary-table { margin-top: 15px; }
        .summary-table th, .summary-table td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        .summary-table th { background-color: #f2f2f2; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
        .signatures { margin-top: 100px; width: 100%; }
        .signatures td { text-align: center; width: 50%; padding-top: 10px; border: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bukti Pengembalian Tabung</h2>

        <table class="info-table">
            <tr>
                <td>Tanggal Pengembalian</td>
                <td>: {{ now()->format('d F Y') }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $pelanggan->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td>: {{ $pelanggan->no_telepon }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $pelanggan->alamat }}, 
                    Kel. {{ $pelanggan->kelurahan->nama_kelurahan ?? '' }}, 
                    Kec. {{ $pelanggan->kelurahan->kecamatan->nama_kecamatan ?? '' }},
                    Kab. {{ $pelanggan->kelurahan->kecamatan->kabupaten->nama_kabupaten ?? '' }},
                    Provinsi {{ $pelanggan->kelurahan->kecamatan->kabupaten->provinsi->nama_provinsi ?? '' }}</td>
            </tr>
        </table>

        <hr>

        <h3>Detail Tabung yang Dikembalikan</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Jumlah</th>
                    <th>Kode Tabung</th>
                    <th>Jenis</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengembalians as $item)
                <tr>
                    <td>1 Buah</td>
                    <td>{{ $item->tabung->kode_tabung }}</td>
                    <td>{{ $item->tabung->jenisTabung->nama_jenis }}</td>
                    <td>{{ $item->statusTabung->status_tabung }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <h3>Ringkasan Pembayaran</h3>
        <table class="summary-table">
            <tr>
                <td>Total Nilai Deposit</td>
                <td style="text-align: right;">Rp {{ number_format($total['deposit'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Denda Keterlambatan</td>
                <td style="text-align: right;">- Rp {{ number_format($total['denda_keterlambatan'], 0, ',', '.') }}</td>
            </tr>
             <tr>
                <td>Total Denda Kondisi Tabung</td>
                <td style="text-align: right;">- Rp {{ number_format($total['denda_kondisi'], 0, ',', '.') }}</td>
            </tr>
             <tr>
                <td>Biaya Administrasi</td>
                <td style="text-align: right;">- Rp {{ number_format(50000 * $pengembalians->count(), 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Sisa Deposit (Dikembalikan)</td>
                <td style="text-align: right;">Rp {{ number_format($total['sisa_deposit'], 0, ',', '.') }}</td>
            </tr>
             @if($total['bayar_tagihan'] > 0)
            <tr class="total-row" style="color: red;">
                <td>KEKURANGAN BAYAR</td>
                <td style="text-align: right;">Rp {{ number_format($total['bayar_tagihan'], 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>

        <table class="signatures">
            <tr>
                <td>(...........................................)</td>
                <td>(...........................................)</td>
            </tr>
            <tr>
                <td><strong>Peminjam</strong></td>
                <td><strong>Laris Jaya Gas</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
