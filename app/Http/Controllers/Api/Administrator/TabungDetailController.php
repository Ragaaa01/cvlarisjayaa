<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;

class TabungDetailController extends Controller
{
    /**
     * Menghasilkan dan mengembalikan file PDF yang berisi detail dan QR code tabung.
     */
    public function downloadQrPdf($id_tabung)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung'])->findOrFail($id_tabung);

            // [PERBAIKAN] Generate QR Code sebagai SVG lalu di-encode ke Base64.
            // Metode ini tidak memerlukan ekstensi PHP 'imagick' atau 'gd'.
            $qrCodeSvg = QrCode::format('svg')->size(150)->generate($tabung->kode_tabung);
            $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

            // Data yang akan dikirim ke view PDF
            $data = [
                'tabung' => $tabung,
                'qrCodeBase64' => $qrCodeBase64, // Kirim data Base64 ke view
            ];

            // Membuat PDF dari file view
            $pdf = Pdf::loadView('pdfs.tabung_qr', $data);

            // Mengatur nama file saat diunduh
            $fileName = 'QR_Code_Tabung_' . $tabung->kode_tabung . '.pdf';

            // Mengembalikan PDF sebagai respons unduhan
            return $pdf->download($fileName);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PDF: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
