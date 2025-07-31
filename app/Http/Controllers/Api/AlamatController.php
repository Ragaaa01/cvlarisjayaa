<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    /**
     * Mengambil daftar semua kecamatan.
     */
    public function getKecamatans()
    {
        // Kita asumsikan untuk saat ini hanya untuk kabupaten Indramayu (ID 1)
        $kecamatans = Kecamatan::where('id_kabupaten', 1)->orderBy('nama_kecamatan')->get();
        return response()->json(['success' => true, 'data' => $kecamatans]);
    }

    /**
     * Mengambil daftar kelurahan berdasarkan ID kecamatan yang diberikan.
     */
    public function getKelurahansByKecamatan($id_kecamatan)
    {
        $kelurahans = Kelurahan::where('id_kecamatan', $id_kecamatan)->orderBy('nama_kelurahan')->get();
        return response()->json(['success' => true, 'data' => $kelurahans]);
    }
}
