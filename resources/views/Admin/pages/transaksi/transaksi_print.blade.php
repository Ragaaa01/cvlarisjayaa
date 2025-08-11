<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota Transaksi #{{ $transaksi->id_transaksi }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .nota-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .nota-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .nota-header h2 {
            color: #014A7F;
            margin: 0;
        }
        .nota-header p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #555;
        }
        .nota-details .row {
            display: flex;
            margin-bottom: 10px;
        }
        .nota-details .label {
            font-weight: bold;
            width: 30%;
        }
        .nota-details .value {
            width: 70%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 0.9rem;
        }
        .table th {
            background-color: #014A7F;
            color: white;
            text-align: center;
        }
        .table .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .status-label {
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .status-belum-dibayar {
            background-color: #dc3545;
        }
        .status-belum-lunas {
            background-color: #ffc107;
        }
        .status-lunas {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="nota-container">
        <div class="nota-header">
            <h2>Nota Pembayaran</h2>
            <p>CV Laris Jaya Gas</p>
            <p>Transaksi #{{ $transaksi->id_transaksi }}</p>
            <p>Tanggal: {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</p>
        </div>
        <div class="nota-details">
            <div class="row">
                <div class="label">ID Transaksi</div>
                <div class="value">{{ $transaksi->id_transaksi }}</div>
            </div>
            <div class="row">
                <div class="label">Pelanggan</div>
                <div class="value">{{ $transaksi->orang->nama_lengkap }}</div>
            </div>
            <div class="row">
                <div class="label">Tanggal</div>
                <div class="value">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</div>
            </div>
            <div class="row">
                <div class="label">Waktu</div>
                <div class="value">{{ $transaksi->waktu_transaksi }}</div>
            </div>
            <div class="row">
                <div class="label">Status Transaksi</div>
                <div class="value">{{ $transaksi->status_valid ? 'Valid' : 'Batal' }}</div>
            </div>
            @php
                $statusPembayaran = 'Belum Dibayar';
                $statusClass = 'status-belum-dibayar';
                if ($pembayaran) {
                    if ($pembayaran->jumlah_pembayaran >= $pembayaran->total_transaksi) {
                        $statusPembayaran = 'Lunas';
                        $statusClass = 'status-lunas';
                    } elseif ($pembayaran->jumlah_pembayaran > 0 || $pembayaran->metode_pembayaran !== 'Belum Dibayar') {
                        $statusPembayaran = 'Belum Lunas';
                        $statusClass = 'status-belum-lunas';
                    }
                }
            @endphp
            <div class="row">
                <div class="label">Status Pembayaran</div>
                <div class="value">
                    <span class="status-label {{ $statusClass }}">{{ $statusPembayaran }}</span>
                </div>
            </div>
            @if ($pembayaran)
                <div class="row">
                    <div class="label">Total Transaksi</div>
                    <div class="value">Rp {{ number_format($pembayaran->total_transaksi, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="label">Jumlah Dibayar</div>
                    <div class="value">Rp {{ number_format($pembayaran->jumlah_pembayaran, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="label">Sisa Tagihan</div>
                    <div class="value">Rp {{ number_format($pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="label">Metode Pembayaran</div>
                    <div class="value">{{ $pembayaran->metode_pembayaran }}</div>
                </div>
                <div class="row">
                    <div class="label">Tanggal Pembayaran</div>
                    <div class="value">{{ \Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d-m-Y') }}</div>
                </div>
            @endif
        </div>

        <h5 style="margin-top: 20px;">Detail Transaksi</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Jenis Transaksi</th>
                    <th>Tabung</th>
                    <th>Harga Pinjam</th>
                    <th>Harga Isi Ulang</th>
                    <th>Nilai Deposit</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalHarga = 0;
                @endphp
                @foreach ($transaksi->transaksiDetails as $detail)
                    <tr>
                        <td>{{ $detail->jenisTransaksiDetail->jenis_transaksi }}</td>
                        <td>{{ $detail->tabung ? $detail->tabung->kode_tabung . ' (' . $detail->tabung->jenisTabung->nama_jenis . ')' : '-' }}</td>
                        @if (strtolower($detail->jenisTransaksiDetail->jenis_transaksi) === 'peminjaman' && $detail->tabung && $detail->tabung->jenisTabung)
                            <td>Rp {{ number_format($detail->tabung->jenisTabung->harga_pinjam, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->tabung->jenisTabung->harga_isi_ulang, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->tabung->jenisTabung->nilai_deposit, 2, ',', '.') }}</td>
                        @elseif (in_array(strtolower($detail->jenisTransaksiDetail->jenis_transaksi), ['isi ulang', 'isi_ulang']))
                            <td>-</td>
                            <td>Rp {{ number_format($detail->harga, 2, ',', '.') }}</td>
                            <td>-</td>
                        @else
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        @endif
                        <td>Rp {{ number_format($detail->harga, 2, ',', '.') }}</td>
                    </tr>
                    @php
                        $totalHarga += $detail->harga;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">Total</td>
                    <td>Rp {{ number_format($totalHarga, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>