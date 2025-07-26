<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\StatusTabung;
use Illuminate\Http\Request;
use Exception;

class StatusTabungController extends Controller
{
    /**
     * Menampilkan daftar semua status tabung.
     */
    public function index()
    {
        try {
            $statusTabungs = StatusTabung::all();
            return response()->json([
                'success' => true,
                'message' => 'Data status tabung berhasil diambil.',
                'data' => $statusTabungs
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }
}
