<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\StatusTabung;
use App\Models\Tabung;
use App\Models\TransaksiDetail;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    /**
     * Menampilkan daftar pengembalian dengan server-side DataTables dan memeriksa pengembalian otomatis.
     */
    public function index(Request $request)
    {
        try {
            // Periksa pengembalian yang sudah melewati batas waktu (deposit habis)
            $this->checkOverdueReturns();
            // Perbarui tanggal pinjam berdasarkan transaksi isi ulang terbaru
            $this->updateTanggalPinjamDariTransaksi();

            if ($request->ajax()) {
                $status = $request->query('status', 'berlangsung');
                $query = Pengembalian::with(['tabung.jenisTabung', 'transaksiDetail.transaksi.orang'])
                    ->select('pengembalians.*');

                if ($status === 'berlangsung') {
                    $query->whereNull('tanggal_pengembalian');
                } elseif ($status === 'selesai') {
                    $query->whereNotNull('tanggal_pengembalian');
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_pelanggan', function ($pengembalian) {
                        return $pengembalian->transaksiDetail && $pengembalian->transaksiDetail->transaksi && $pengembalian->transaksiDetail->transaksi->orang
                            ? $pengembalian->transaksiDetail->transaksi->orang->nama_lengkap
                            : '-';
                    })
                    ->addColumn('kode_tabung', function ($pengembalian) {
                        return $pengembalian->tabung ? $pengembalian->tabung->kode_tabung : '-';
                    })
                    ->addColumn('nama_jenis_tabung', function ($pengembalian) {
                        return $pengembalian->tabung && $pengembalian->tabung->jenisTabung
                            ? $pengembalian->tabung->jenisTabung->nama_jenis
                            : '-';
                    })
                    ->addColumn('tanggal_pinjam', function ($pengembalian) {
                        return $pengembalian->tanggal_pinjam instanceof \Carbon\Carbon
                            ? $pengembalian->tanggal_pinjam->format('Y-m-d')
                            : ($pengembalian->tanggal_pinjam ? substr($pengembalian->tanggal_pinjam, 0, 10) : '-');
                    })
                    ->addColumn('tanggal_pengembalian', function ($pengembalian) {
                        return $pengembalian->tanggal_pengembalian instanceof \Carbon\Carbon
                            ? $pengembalian->tanggal_pengembalian->format('Y-m-d')
                            : ($pengembalian->tanggal_pengembalian ? substr($pengembalian->tanggal_pengembalian, 0, 10) : '-');
                    })
                    ->addColumn('sisa_deposit', function ($pengembalian) {
                        return 'Rp ' . number_format($pengembalian->sisa_deposit, 2, ',', '.');
                    })
                    ->addColumn('total_denda', function ($pengembalian) {
                        return 'Rp ' . number_format($pengembalian->total_denda, 2, ',', '.');
                    })
                    ->addColumn('action', function ($pengembalian) {
                        $actionButtons = '
                            <div class="action-buttons">
                                <a href="' . route('pengembalian.show', $pengembalian->id_pengembalian) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>';
                        if (is_null($pengembalian->tanggal_pengembalian)) {
                            $actionButtons .= '
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#pengembalianModal"
                                    data-id="' . $pengembalian->id_pengembalian . '"
                                    data-kode-tabung="' . ($pengembalian->tabung ? $pengembalian->tabung->kode_tabung : '-') . '"
                                    data-deposit="' . $pengembalian->deposit . '">
                                    <i class="fas fa-undo action-icon"></i>
                                </button>';
                        }
                        $actionButtons .= '</div>';
                        return $actionButtons;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('admin.pages.pengembalian.index');
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar pengembalian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar pengembalian. Silakan coba lagi.');
        }
    }

    /**
     * Memeriksa pengembalian yang sudah melewati batas waktu (deposit habis) dan menandainya sebagai hilang.
     */
    protected function checkOverdueReturns()
    {
        DB::beginTransaction();
        try {
            $pengembalians = Pengembalian::whereNull('tanggal_pengembalian')->get();
            $statusHilang = StatusTabung::where('status_tabung', 'hilang')->first();

            if (!$statusHilang) {
                throw new \Exception('Status tabung "hilang" tidak ditemukan.');
            }

            foreach ($pengembalians as $pengembalian) {
                $tanggalPinjam = Carbon::parse($pengembalian->tanggal_pinjam);
                $selisihHari = $tanggalPinjam->diffInDays(Carbon::now());
                $periodeKeterlambatan = floor(($selisihHari - 30) / 31);
                $dendaKeterlambatan = max(0, $periodeKeterlambatan) * 50000;

                if ($dendaKeterlambatan >= $pengembalian->deposit) {
                    $pengembalian->update([
                        'tanggal_pengembalian' => Carbon::now()->toDateString(),
                        'waktu_pengembalian' => Carbon::now()->toTimeString(),
                        'jumlah_keterlambatan_bulan' => $periodeKeterlambatan,
                        'total_denda' => $pengembalian->deposit,
                        'denda_kondisi_tabung' => $pengembalian->deposit,
                        'biaya_admin' => 0, // Tidak ada biaya admin untuk pengembalian otomatis
                        'sisa_deposit' => 0,
                        'id_status_tabung' => $statusHilang->id_status_tabung,
                    ]);

                    $pengembalian->tabung->update(['id_status_tabung' => $statusHilang->id_status_tabung]);
                    Log::info('Pengembalian otomatis ID ' . $pengembalian->id_pengembalian . ' ditandai sebagai hilang karena deposit habis.');
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memeriksa pengembalian yang terlambat: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui tanggal pinjam berdasarkan transaksi isi ulang terbaru untuk tabung yang masih dipinjam.
     */
    protected function updateTanggalPinjamDariTransaksi()
    {
        DB::beginTransaction();
        try {
            $pengembalians = Pengembalian::whereNull('tanggal_pengembalian')->get();

            foreach ($pengembalians as $pengembalian) {
                // Cari transaksi isi ulang terbaru untuk tabung yang sama
                $latestTransaksiDetail = TransaksiDetail::where('id_tabung', $pengembalian->id_tabung)
                    ->join('transaksis', 'transaksi_details.id_transaksi', '=', 'transaksis.id_transaksi')
                    ->orderBy('transaksis.tanggal_transaksi', 'desc')
                    ->first();

                if ($latestTransaksiDetail) {
                    $tanggalTransaksi = Carbon::parse($latestTransaksiDetail->transaksi->tanggal_transaksi);
                    if ($tanggalTransaksi->gt(Carbon::parse($pengembalian->tanggal_pinjam))) {
                        $pengembalian->update([
                            'tanggal_pinjam' => $tanggalTransaksi->toDateString(),
                            'waktu_pinjam' => $tanggalTransaksi->toTimeString(),
                        ]);
                        Log::info('Tanggal pinjam untuk pengembalian ID ' . $pengembalian->id_pengembalian . ' diperbarui ke ' . $tanggalTransaksi->toDateString() . ' berdasarkan transaksi isi ulang.');
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui tanggal pinjam dari transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail pengembalian.
     */
    public function show($id)
    {
        try {
            $pengembalian = Pengembalian::with(['tabung.jenisTabung', 'transaksiDetail.transaksi.orang', 'statusTabung'])
                ->findOrFail($id);
            return view('admin.pages.pengembalian.show', compact('pengembalian'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail pengembalian: ' . $e->getMessage());
            return redirect()->route('pengembalian.index')->with('error', 'Gagal memuat detail pengembalian.');
        }
    }

    /**
     * Memperbarui data pengembalian (proses pengembalian tabung).
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kondisi_tabung' => 'required|in:Baik,Rusak,Hilang',
            'denda_kondisi_tabung' => 'required_if:kondisi_tabung,Rusak,Hilang|numeric|min:0',
            'biaya_admin' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $pengembalian = Pengembalian::findOrFail($id);

            if (!is_null($pengembalian->tanggal_pengembalian)) {
                $message = 'Tabung sudah dikembalikan.';
                if ($request->ajax()) {
                    return response()->json(['error' => $message], 422);
                }
                return redirect()->route('pengembalian.index')->with('error', $message);
            }

            // Pastikan tabung terkait ada
            if (!$pengembalian->tabung) {
                throw new \Exception('Tabung tidak ditemukan untuk pengembalian ini.');
            }

            $now = Carbon::now();
            $tanggalPinjam = Carbon::parse($pengembalian->tanggal_pinjam);
            $selisihHari = $tanggalPinjam->diffInDays($now);
            $periodeKeterlambatan = floor(($selisihHari - 30) / 31); // Mulai terhitung setelah 30 hari
            $jumlahKeterlambatanPeriode = max(0, $periodeKeterlambatan); // Pastikan tidak negatif
            $dendaKeterlambatan = $jumlahKeterlambatanPeriode * 50000; // Rp 50.000 per periode 31 hari
            $deposit = floatval($pengembalian->deposit);

            // Tentukan biaya admin dan denda kondisi tabung berdasarkan kondisi
            $biayaAdmin = $request->kondisi_tabung === 'Hilang' ? 0 : 50000; // Biaya admin 0 jika hilang
            $dendaKondisiTabung = $request->kondisi_tabung === 'Hilang' ? $deposit : ($request->kondisi_tabung === 'Baik' ? 0 : floatval($request->denda_kondisi_tabung));
            $totalDenda = $dendaKeterlambatan + $dendaKondisiTabung;

            // Tentukan status tabung berdasarkan kondisi
            $statusMap = [
                'Baik' => 'tersedia',
                'Rusak' => 'rusak',
                'Hilang' => 'hilang',
            ];
            $statusTabungValue = $statusMap[$request->kondisi_tabung] ?? 'tersedia';
            $statusTabung = StatusTabung::where('status_tabung', $statusTabungValue)->first();
            if (!$statusTabung) {
                throw new \Exception('Status tabung "' . $statusTabungValue . '" tidak ditemukan di database.');
            }

            // Hitung sisa deposit, batasi total denda agar tidak melebihi deposit
            if ($request->kondisi_tabung === 'Hilang' || $totalDenda + $biayaAdmin >= $deposit) {
                // Jika tabung hilang atau total denda + biaya admin melebihi deposit, ambil seluruh deposit
                $dendaKondisiTabung = $deposit;
                $totalDenda = $deposit;
                $sisaDeposit = 0;
                $biayaAdmin = 0; // Pastikan biaya admin 0 jika deposit habis
                $statusTabungValue = 'hilang';
                $statusTabung = StatusTabung::where('status_tabung', $statusTabungValue)->first();
                if (!$statusTabung) {
                    throw new \Exception('Status tabung "hilang" tidak ditemukan di database.');
                }
            } else {
                // Jika deposit belum habis, hitung seperti biasa
                $sisaDeposit = $deposit - ($totalDenda + $biayaAdmin);
            }

            $pengembalian->update([
                'tanggal_pengembalian' => $now->toDateString(),
                'waktu_pengembalian' => $now->toTimeString(),
                'jumlah_keterlambatan_bulan' => $jumlahKeterlambatanPeriode,
                'total_denda' => $totalDenda,
                'denda_kondisi_tabung' => $dendaKondisiTabung,
                'biaya_admin' => $biayaAdmin,
                'sisa_deposit' => $sisaDeposit,
                'id_status_tabung' => $statusTabung->id_status_tabung,
            ]);

            // Update status tabung di tabel tabungs
            $pengembalian->tabung->update(['id_status_tabung' => $statusTabung->id_status_tabung]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Tabung ' . $pengembalian->tabung->kode_tabung . ' berhasil dikembalikan.',
                    'kode_tabung' => $pengembalian->tabung->kode_tabung,
                    'redirect' => route('pengembalian.index')
                ]);
            }
            return redirect()->route('pengembalian.index')->with('success', 'Tabung ' . $pengembalian->tabung->kode_tabung . ' berhasil dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses pengembalian ID ' . $id . ': ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Gagal memproses pengembalian: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}