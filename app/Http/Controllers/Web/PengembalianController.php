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
use Barryvdh\DomPDF\Facade\Pdf;

class PengembalianController extends Controller
{
    /**
     * Menampilkan daftar pengembalian yang sedang berlangsung dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            // Periksa pengembalian yang sudah melewati batas waktu (deposit habis)
            $this->checkOverdueReturns();
            // Perbarui tanggal pinjam berdasarkan transaksi isi ulang terbaru
            $this->updateTanggalPinjamDariTransaksi();

            if ($request->ajax()) {
                $query = Pengembalian::with(['tabung.jenisTabung', 'transaksiDetail.transaksi.orang'])
                    ->select('pengembalians.*')
                    ->whereNull('tanggal_pengembalian'); // Hanya pengembalian yang sedang berlangsung

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
                                </a>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#pengembalianModal"
                                    data-id="' . $pengembalian->id_pengembalian . '"
                                    data-kode-tabung="' . ($pengembalian->tabung ? $pengembalian->tabung->kode_tabung : '-') . '"
                                    data-deposit="' . $pengembalian->deposit . '">
                                    <i class="fas fa-undo action-icon"></i>
                                </button>
                            </div>';
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

    // Metode checkOverdueReturns dan updateTanggalPinjamDariTransaksi tetap sama
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
                        'biaya_admin' => 0,
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

    protected function updateTanggalPinjamDariTransaksi()
    {
        DB::beginTransaction();
        try {
            $pengembalians = Pengembalian::whereNull('tanggal_pengembalian')->get();

            foreach ($pengembalians as $pengembalian) {
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

    // Metode show, update, dan print tetap sama
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

            if (!$pengembalian->tabung) {
                throw new \Exception('Tabung tidak ditemukan untuk pengembalian ini.');
            }

            $now = Carbon::now();
            $tanggalPinjam = Carbon::parse($pengembalian->tanggal_pinjam);
            $selisihHari = $tanggalPinjam->diffInDays($now);
            $periodeKeterlambatan = floor(($selisihHari - 30) / 31);
            $jumlahKeterlambatanPeriode = max(0, $periodeKeterlambatan);
            $dendaKeterlambatan = $jumlahKeterlambatanPeriode * 50000;
            $deposit = floatval($pengembalian->deposit);

            $biayaAdmin = $request->kondisi_tabung === 'Hilang' ? 0 : floatval($request->biaya_admin);
            $dendaKondisiTabung = $request->kondisi_tabung === 'Hilang' ? $deposit : ($request->kondisi_tabung === 'Baik' ? 0 : floatval($request->denda_kondisi_tabung));
            $totalDenda = $dendaKeterlambatan + $dendaKondisiTabung;

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

            if ($request->kondisi_tabung === 'Hilang' || $totalDenda + $biayaAdmin >= $deposit) {
                $dendaKondisiTabung = $deposit;
                $totalDenda = $deposit;
                $sisaDeposit = 0;
                $biayaAdmin = 0;
                $statusTabungValue = 'hilang';
                $statusTabung = StatusTabung::where('status_tabung', $statusTabungValue)->first();
                if (!$statusTabung) {
                    throw new \Exception('Status tabung "hilang" tidak ditemukan di database.');
                }
            } else {
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

            $pengembalian->tabung->update(['id_status_tabung' => $statusTabung->id_status_tabung]);

            DB::commit();

            $successMessage = 'Tabung ' . $pengembalian->tabung->kode_tabung . ' berhasil dikembalikan.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => $successMessage,
                    'redirect' => route('pengembalian.show', $pengembalian->id_pengembalian)
                ]);
            }
            return redirect()->route('pengembalian.show', $pengembalian->id_pengembalian)->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memproses pengembalian ID ' . $id . ': ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Gagal memproses pengembalian: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $pengembalian = Pengembalian::with(['tabung.jenisTabung', 'transaksiDetail.transaksi.orang', 'statusTabung'])
                ->findOrFail($id);
            $pdf = Pdf::loadView('admin.pages.pengembalian.pengembalian_print', compact('pengembalian'));
            return $pdf->download('nota_pengembalian_' . $pengembalian->id_pengembalian . '_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mencetak nota pengembalian ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak nota pengembalian ke PDF: ' . $e->getMessage());
        }
    }
}