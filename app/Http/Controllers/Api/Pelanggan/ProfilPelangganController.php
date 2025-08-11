<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfilPelangganController extends Controller
{
    /**
     * Mengambil data profil lengkap dari pengguna yang sedang login, termasuk saldo deposit aktif.
     */
    public function show(Request $request)
    {
        try {
            $akun = $request->user()->load(['orang.kelurahan.kecamatan.kabupaten.provinsi', 'role']);
            $orang = $akun->orang;

            // Menghitung total deposit dari peminjaman yang masih aktif
            $peminjamanAktif = TransaksiDetail::whereHas('transaksi', function ($q) use ($orang) {
                $q->where('id_orang', $orang->id_orang);
            })
                ->whereHas('jenisTransaksiDetail', function ($q) {
                    $q->where('jenis_transaksi', 'peminjaman');
                })
                ->whereDoesntHave('pengembalian')
                ->get();

            $saldoDepositAktif = 0;
            foreach ($peminjamanAktif as $peminjaman) {
                $deposit = TransaksiDetail::where('id_transaksi', $peminjaman->id_transaksi)
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'deposit');
                    })->sum('harga');
                $saldoDepositAktif += $deposit;
            }

            // Menambahkan data saldo ke objek akun sebelum dikirim
            $akun->saldo_deposit_aktif = (float) $saldoDepositAktif;

            return response()->json(['success' => true, 'message' => 'Data profil berhasil diambil.', 'data' => $akun], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data profil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Memperbarui data profil (orang) dari pengguna yang sedang login.
     */
    public function update(Request $request)
    {
        $orang = $request->user()->orang;

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon,' . $orang->id_orang . ',id_orang',
            'id_kelurahan' => 'required|exists:kelurahans,id_kelurahan',
            'alamat' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            $orang->update($request->only(['nama_lengkap', 'no_telepon', 'id_kelurahan', 'alamat']));
            return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui.', 'data' => $orang], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui profil.'], 500);
        }
    }

    /**
     * Mengubah password dari pengguna yang sedang login.
     */
    public function ubahPassword(Request $request)
    {
        $akun = $request->user();

        $validator = Validator::make($request->all(), [
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        if (!Hash::check($request->password_lama, $akun->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama tidak sesuai.'], 401);
        }

        try {
            $akun->update(['password' => Hash::make($request->password_baru)]);
            return response()->json(['success' => true, 'message' => 'Password berhasil diubah.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengubah password.'], 500);
        }
    }

    public function lengkapiProfil(Request $request)
    {
        $akun = $request->user();
        $orang = $akun->orang;

        // Validasi untuk data diri yang lengkap
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:orangs,nik,' . $orang->id_orang . ',id_orang',
            'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon,' . $orang->id_orang . ',id_orang',
            'id_kelurahan' => 'required|exists:kelurahans,id_kelurahan',
            'alamat' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'data' => ['errors' => $validator->errors()]], 422);
        }

        try {
            // Update data Orang yang sebelumnya hanya placeholder
            $orang->update($request->all());

            return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui.', 'data' => $akun->load('orang')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui profil: ' . $e->getMessage()], 500);
        }
    }
}
