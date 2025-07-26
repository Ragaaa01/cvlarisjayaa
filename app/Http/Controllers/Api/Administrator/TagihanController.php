<?php

// File: app/Http/Controllers/Api/Administrator/TagihanController.php

namespace App\Http\Controllers\Api\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\PembayaranTagihan;
use App\Models\Deposit;
use App\Models\RiwayatDeposit;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TagihanController extends Controller
{
    // ... (metode lain seperti index, show, dll. bisa ada di sini)

    /**
     * Menampilkan daftar semua tagihan dengan filter dan pencarian.
     * URL: GET /api/administrator/tagihan
     * Query Params: ?status=belum_lunas&search=nama_pelanggan
     */
    public function index(Request $request)
    {
        try {
            $query = Tagihan::with(['akun.orang']);

            // Filter berdasarkan status tagihan
            if ($request->has('status')) {
                $query->where('status_tagihan', $request->query('status'));
            }

            // Fitur pencarian berdasarkan nama atau nomor telepon pelanggan
            if ($request->has('search')) {
                $searchTerm = $request->query('search');
                $query->whereHas('akun.orang', function ($q) use ($searchTerm) {
                    $q->where('nama_lengkap', 'like', '%' . $searchTerm . '%')
                        ->orWhere('no_telepon', 'like', '%' . $searchTerm . '%');
                });
            }

            $tagihans = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Daftar tagihan berhasil diambil.',
                'data' => $tagihans
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tagihan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu tagihan spesifik.
     * URL: GET /api/administrator/tagihan/{id_tagihan}
     */
    public function show($id_tagihan)
    {
        try {
            $tagihan = Tagihan::with([
                'akun.orang',
                'pembayaranTagihans', // Menampilkan riwayat pembayaran tagihan ini
                'peminjamans',        // Menampilkan peminjaman yang terkait
                'pengisians'          // Menampilkan isi ulang yang terkait
            ])->findOrFail($id_tagihan);

            return response()->json([
                'success' => true,
                'message' => 'Detail tagihan berhasil diambil.',
                'data' => $tagihan
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan.'
            ], 404);
        }
    }
    /**
     * Mengonfirmasi pembayaran tunai untuk sebuah tagihan.
     * Logika ini meniru apa yang dilakukan oleh MidtransWebhookController.
     */
    public function konfirmasiPembayaranTunai(Request $request, $id_tagihan)
    {
        DB::beginTransaction();
        try {
            $tagihan = Tagihan::findOrFail($id_tagihan);

            // 1. Validasi: Pastikan tagihan belum lunas
            if ($tagihan->status_tagihan === 'lunas') {
                return response()->json(['success' => false, 'message' => 'Tagihan ini sudah lunas.'], 400);
            }

            // 2. Update status tagihan menjadi lunas
            $tagihan->update([
                'status_tagihan' => 'lunas',
                'jumlah_dibayar' => $tagihan->total_tagihan,
                'sisa' => 0,
            ]);

            // 3. Catat di tabel pembayaran_tagihans
            PembayaranTagihan::create([
                'id_tagihan' => $tagihan->id_tagihan,
                'jumlah_dibayar' => $tagihan->total_tagihan,
                'metode_pembayaran' => 'tunai', // Metode pembayaran di-set sebagai 'tunai'
                'tanggal_bayar' => now(),
                // 'nomor_referensi' bisa diisi ID admin yang memproses atau dibiarkan null
            ]);

            // 4. Jika ada jumlah top-up pada tagihan, tambahkan ke saldo deposit
            if ($tagihan->jumlah_top_up > 0) {
                $deposit = $tagihan->akun->deposit;
                if (!$deposit) {
                    $deposit = Deposit::create(['id_akun' => $tagihan->id_akun]);
                }
                $deposit->increment('saldo', $tagihan->jumlah_top_up);

                RiwayatDeposit::create([
                    'id_deposit' => $deposit->id_deposit,
                    'jenis_aktivitas' => 'top_up',
                    'jumlah' => $tagihan->jumlah_top_up,
                    'keterangan' => 'Top-up tunai dari transaksi #' . $tagihan->id_tagihan,
                    'waktu_aktivitas' => now(),
                ]);
            }

            // 5. Aktifkan status peminjaman yang terkait dengan tagihan ini
            Peminjaman::where('id_tagihan', $tagihan->id_tagihan)
                ->update(['status_pinjam' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tunai berhasil dikonfirmasi. Pesanan siap untuk disiapkan.',
                'data' => $tagihan
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal mengonfirmasi pembayaran: ' . $e->getMessage()], 500);
        }
    }
}
