<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Kepemilikan;
use Exception;
use Illuminate\Http\Request;

class KepemilikanController extends Controller
{
    /**
     * Menampilkan daftar semua jenis kepemilikan.
     * Endpoint ini digunakan untuk mengisi pilihan di form frontend.
     */
    public function index()
    {
        try {
            $data = Kepemilikan::all();

            return response()->json([
                'success' => true,
                'message' => 'Data kepemilikan berhasil diambil.',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kepemilikan: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
