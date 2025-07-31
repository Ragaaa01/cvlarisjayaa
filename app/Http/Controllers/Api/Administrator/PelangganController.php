<?php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Deposit;
use App\Models\Orang;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar semua pelanggan dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        try {
            // [PERBAIKAN] Memuat relasi alamat secara lengkap
            $query = Akun::with(['orang.kelurahan.kecamatan.kabupaten.provinsi', 'orang.mitras', 'role'])
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

            $pelanggan = $query->latest('created_at')->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diambil.',
                'data'    => $pelanggan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pelanggan',
                'data'    => ['errors' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Membuat akun baru untuk Pelanggan Non-Aplikasi (datang langsung).
     */
    public function store(Request $request)
    {
        $messages = [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'no_telepon.required'   => 'Nomor telepon wajib diisi.',
            'no_telepon.unique'     => 'Nomor telepon ini sudah terdaftar.',
            'nik.unique'            => 'NIK ini sudah terdaftar.',
            'id_kelurahan.exists'   => 'Kelurahan yang dipilih tidak valid.',
        ];

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'required|string|unique:orangs,no_telepon|max:15',
            'id_kelurahan' => 'nullable|exists:kelurahans,id_kelurahan',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|unique:orangs,nik|max:20',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $orang = Orang::create($request->all());

            // Role 'pelanggan' diasumsikan id=2
            $akun = Akun::create([
                'id_role' => 2,
                'id_orang' => $orang->id_orang,
                'email' => $orang->nik . '@nonaplikasi.com',
                'password' => Hash::make(uniqid()),
                'status_aktif' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pelanggan non-aplikasi berhasil ditambahkan.',
                'data'    => $akun->load('orang')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pelanggan',
                'data'    => ['errors' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Menampilkan data detail satu pelanggan, termasuk rekapitulasi keuangan dan alamat lengkap.
     */
    public function show($id_akun)
    {
        try {
            $akun = Akun::with(['orang.kelurahan.kecamatan.kabupaten.provinsi', 'orang.mitras'])->findOrFail($id_akun);
            $orang = $akun->orang;

            // Hitung rekapitulasi keuangan
            $totalUtang = Transaksi::where('id_orang', $orang->id_orang)->where('status_valid', true)->sum('total_transaksi');
            $totalBayar = Pembayaran::where('id_orang', $orang->id_orang)->sum('jumlah_pembayaran');
            $sisaTagihan = $totalUtang - $totalBayar;

            // Ambil riwayat transaksi dan pembayaran terakhir
            $riwayatTransaksi = Transaksi::where('id_orang', $orang->id_orang)->latest()->take(5)->get();
            $riwayatPembayaran = Pembayaran::where('id_orang', $orang->id_orang)->latest()->take(5)->get();

            $data = [
                'akun' => $akun,
                'rekapitulasi_keuangan' => [
                    'total_utang' => (float) $totalUtang,
                    'total_pembayaran' => (float) $totalBayar,
                    'sisa_tagihan' => (float) $sisaTagihan,
                ],
                'riwayat_transaksi' => $riwayatTransaksi,
                'riwayat_pembayaran' => $riwayatPembayaran,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail pelanggan berhasil diambil.',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.',
                'data'    => ['errors' => $e->getMessage()]
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
            'id_kelurahan' => 'nullable|exists:kelurahans,id_kelurahan',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|max:20|unique:orangs,nik,' . $id_orang . ',id_orang',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        try {
            $orang = Orang::findOrFail($id_orang);
            $orang->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diperbarui.',
                'data'    => $orang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data.',
                'data'    => ['errors' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Mengaktifkan akses aplikasi untuk pelanggan non-aplikasi dengan mengatur email & password baru.
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
                'data'    => ['errors' => $validator->errors()]
            ], 422);
        }

        try {
            $akun = Akun::findOrFail($id_akun);

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
                'data'    => $akun
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal aktivasi atau akun tidak ditemukan.',
                'data'    => ['errors' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * [BARU] Menampilkan daftar pelanggan yang memiliki peminjaman aktif (belum dikembalikan).
     */
    public function getPelangganAktif(Request $request)
    {
        try {
            // 1. Cari semua detail transaksi peminjaman yang belum memiliki record pengembalian.
            $peminjamanAktifDetailIds = TransaksiDetail::whereHas('jenisTransaksiDetail', function ($q) {
                $q->where('jenis_transaksi', 'peminjaman');
            })
                ->whereDoesntHave('pengembalian')
                ->pluck('id_transaksi');

            // 2. Dari transaksi tersebut, ambil semua id_orang yang unik.
            $orangIds = Transaksi::whereIn('id_transaksi', $peminjamanAktifDetailIds)
                ->distinct()
                ->pluck('id_orang');

            // 3. Ambil data akun dari id_orang tersebut.
            $query = Akun::with(['orang.kelurahan.kecamatan', 'orang.mitras'])
                ->whereIn('id_orang', $orangIds);

            // Logika pencarian
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->whereHas('orang', function ($q) use ($searchTerm) {
                    $q->where('nama_lengkap', 'like', '%' . $searchTerm . '%')
                        ->orWhere('no_telepon', 'like', '%' . $searchTerm . '%')
                        ->orWhere('nik', 'like', '%' . $searchTerm . '%');
                });
            }

            $pelanggan = $query->latest('created_at')->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan dengan peminjaman aktif berhasil diambil.',
                'data'    => $pelanggan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pelanggan aktif: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
