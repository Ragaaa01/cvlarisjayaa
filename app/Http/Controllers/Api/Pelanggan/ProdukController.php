<?php

namespace App\Http\Controllers\Api\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use App\Models\TransaksiDetail;
use Exception;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = JenisTabung::query();

            // Fitur pencarian berdasarkan nama jenis tabung
            if ($request->has('search')) {
                $query->where('nama_jenis', 'like', '%' . $request->search . '%');
            }

            $produk = $query->get();

            // [PERBAIKAN] Menggunakan map untuk membuat data baru yang lebih aman
            $dataWithStock = $produk->map(function ($jenisTabung) {
                // 1. Hitung stok fisik yang tersedia
                $stokFisik = $jenisTabung->tabungs()->where('id_status_tabung', 1)->count(); // Asumsi 1 = 'tersedia'

                // 2. Hitung item peminjaman yang sudah dibayar tapi belum dikembalikan
                $peminjamanAktif = TransaksiDetail::whereHas('transaksi', function ($q) {
                        $q->where('status_valid', true); // Sudah dibayar
                    })
                    ->whereHas('jenisTransaksiDetail', function ($q) {
                        $q->where('jenis_transaksi', 'peminjaman');
                    })
                    ->whereHas('tabung', function ($q) use ($jenisTabung) {
                        $q->where('id_jenis_tabung', $jenisTabung->id_jenis_tabung);
                    })
                    ->whereDoesntHave('pengembalian') // Belum dikembalikan
                    ->count();
                
                // 3. Hitung stok virtual
                $stokVirtual = max(0, $stokFisik - $peminjamanAktif);

                // 4. Ubah model menjadi array dan tambahkan properti baru
                $data = $jenisTabung->toArray();
                $data['stok_tersedia'] = $stokVirtual;
                
                return $data;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data produk berhasil diambil.',
                'data'    => $dataWithStock
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}
