<!DOCTYPE html>
<html>
<head>
    <title>QR Code Tabung</title>
    <style>
        body { font-family: sans-serif; text-align: center; }
        .container { border: 2px solid #333; padding: 20px; width: 300px; margin: auto; }
        h1 { margin-top: 0; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laris Jaya Gas</h1>
        <p><strong>Kode Tabung:</strong> {{ $tabung->kode_tabung }}</p>
        <p><strong>Jenis:</strong> {{ $tabung->jenisTabung->nama_jenis }}</p>
        <hr>
        
        <!-- [PERBAIKAN] Tampilkan QR Code menggunakan tag <img> dengan data Base64 -->
        <img src="{{ $qrCodeBase64 }}" alt="QR Code">

        <p style="margin-top: 10px;">Pindai untuk identifikasi</p>
    </div>
</body>
</html>