<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use Illuminate\Http\Request;
use Exception;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar semua jenis tabung yang bisa dipinjam/diisi ulang.
     * Ini berfungsi sebagai katalog produk untuk aplikasi pelanggan.
     */
    public function index()
    {
        try {
            // Mengambil semua jenis tabung yang aktif (tidak di-soft delete)
            $produk = JenisTabung::orderBy('nama_jenis', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Katalog produk berhasil diambil.',
                'data' => $produk
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk: ' . $e->getMessage()
            ], 500);
        }
    }
}
