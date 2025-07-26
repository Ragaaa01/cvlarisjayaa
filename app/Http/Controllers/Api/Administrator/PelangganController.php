<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Orang;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar semua pelanggan dengan fitur pencarian.
     * Pencarian bisa berdasarkan nama, nomor telepon, atau NIK.
     */
    public function index(Request $request)
    {
        try {
            $query = Akun::with('orang', 'role')
                // Hanya mengambil akun dengan role pelanggan
                ->whereHas('role', function ($q) {
                    $q->where('nama_role', 'pelanggan');
                });

            // Logika pencarian
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->whereHas('orang', function ($q) use ($searchTerm) {
                    $q->where('nama_lengkap', 'like', '%' . $searchTerm . '%')
                        ->orWhere('no_telepon', 'like', '%' . $searchTerm . '%')
                        ->orWhere('nik', 'like', '%' . $searchTerm . '%');
                });
            }

            $pelanggan = $query->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diambil.',
                'data' => $pelanggan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat akun baru untuk Pelanggan Non-Aplikasi (datang langsung).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'required|string|unique:orangs,no_telepon|max:15',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|unique:orangs,nik|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Menggunakan transaksi database untuk memastikan semua data konsisten
        DB::beginTransaction();
        try {
            // 1. Buat data orang
            $orang = Orang::create($request->all());

            // 2. Buat akun untuk orang tersebut
            // Role 'pelanggan' diasumsikan memiliki id = 2
            $akun = Akun::create([
                'id_role' => 2,
                'id_orang' => $orang->id_orang,
                'email' => $orang->no_telepon . '@nonaplikasi.com', // Email dummy
                'password' => Hash::make(uniqid()), // Password dummy
                'status_aktif' => true, // Langsung aktif karena didaftarkan admin
            ]);

            // 3. Buat dompet deposit untuk akun baru
            Deposit::create([
                'id_akun' => $akun->id_akun,
                'saldo' => 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggan non-aplikasi berhasil ditambahkan.',
                'data' => $akun->load('orang')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pelanggan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan data detail satu pelanggan.
     */
    public function show($id_akun)
    {
        try {
            $akun = Akun::with([
                'orang',
                'deposit',
                'tagihans' => function ($query) {
                    $query->latest()->take(5); // Ambil 5 tagihan terakhir
                },
                'peminjamans' => function ($query) {
                    $query->where('status_pinjam', true); // Hanya peminjaman yang aktif
                }
            ])->findOrFail($id_akun);

            return response()->json([
                'success' => true,
                'message' => 'Detail pelanggan berhasil diambil.',
                'data' => $akun
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Memperbarui data diri (orangs) seorang pelanggan.
     */
    public function update(Request $request, $id_orang)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon,' . $id_orang . ',id_orang',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|max:20|unique:orangs,nik,' . $id_orang . ',id_orang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $orang = Orang::findOrFail($id_orang);
            $orang->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diperbarui.',
                'data' => $orang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data atau pelanggan tidak ditemukan.'
            ], 500);
        }
    }

    /**
     * Mengaktifkan akses aplikasi untuk pelanggan.
     * Ini bisa untuk pelanggan baru atau pelanggan non-aplikasi.
     */
    public function aktivasi(Request $request, $id_akun)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:akuns,email,' . $id_akun . ',id_akun',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $akun = Akun::findOrFail($id_akun);

            // Jika data orang belum lengkap, bisa ditambahkan validasi di sini
            if (!$akun->orang || !$akun->orang->nama_lengkap || !$akun->orang->no_telepon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal aktivasi: Data diri pelanggan belum lengkap.'
                ], 400);
            }

            $akun->update([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status_aktif' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun pelanggan berhasil diaktifkan untuk akses aplikasi.',
                'data' => $akun
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal aktivasi atau akun tidak ditemukan.'
            ], 500);
        }
    }
}
