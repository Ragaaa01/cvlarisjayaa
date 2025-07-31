<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\OrangMitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class OrangMitraController extends Controller
{
    /**
     * Menghubungkan seorang individu (orang) sebagai perwakilan dari mitra.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_orang' => [
                'required',
                'exists:orangs,id_orang',
                // Memastikan kombinasi id_orang dan id_mitra unik
                Rule::unique('orang_mitras')->where(function ($query) use ($request) {
                    return $query->where('id_mitra', $request->id_mitra);
                }),
            ],
            'id_mitra' => 'required|exists:mitras,id_mitra',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            $orangMitra = OrangMitra::create([
                'id_orang' => $request->id_orang,
                'id_mitra' => $request->id_mitra,
                'status_valid' => true, // Diasumsikan langsung valid saat ditambahkan admin
            ]);
            return response()->json(['success' => true, 'message' => 'Perwakilan berhasil ditambahkan ke mitra.', 'data' => $orangMitra], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghubungkan perwakilan.', 'data' => null], 500);
        }
    }

    /**
     * Memutuskan hubungan antara seorang perwakilan dan mitra.
     */
    public function destroy($id_orang_mitra)
    {
        try {
            $orangMitra = OrangMitra::findOrFail($id_orang_mitra);
            $orangMitra->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hubungan perwakilan dengan mitra berhasil dihapus.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus hubungan atau data tidak ditemukan.',
                'data'    => null
            ], 500);
        }
    }
}
