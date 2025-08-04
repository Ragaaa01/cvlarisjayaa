<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Orang;
use App\Models\Tabung;
use App\Models\JenisTransaksiDetail;
use App\Models\Pengembalian;
use App\Models\StatusTabung;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Transaksi::with(['orang', 'transaksiDetails']);

                // Filter berdasarkan jenis filter
                if ($request->has('filter_type') && !empty($request->filter_type)) {
                    if ($request->filter_type === 'specific_date' && $request->has('specific_date') && !empty($request->specific_date)) {
                        $query->whereDate('tanggal_transaksi', $request->specific_date);
                    } elseif ($request->filter_type === 'date_range' && $request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                        $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                    } elseif ($request->filter_type === 'month' && $request->has('month') && !empty($request->month)) {
                        $date = Carbon::createFromFormat('Y-m', $request->month);
                        $query->whereMonth('tanggal_transaksi', $date->month)
                              ->whereYear('tanggal_transaksi', $date->year);
                    }
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_orang', function ($transaksi) {
                        return $transaksi->orang ? $transaksi->orang->nama_lengkap : '-';
                    })
                    ->addColumn('tanggal_transaksi', function ($transaksi) {
                        return $transaksi->tanggal_transaksi instanceof \Carbon\Carbon 
                            ? $transaksi->tanggal_transaksi->format('Y-m-d')
                            : ($transaksi->tanggal_transaksi ? substr($transaksi->tanggal_transaksi, 0, 10) : '-');
                    })
                    ->addColumn('status', function ($transaksi) {
                        return $transaksi->status_valid ? 'Valid' : 'Batal';
                    })
                    ->addColumn('action', function ($transaksi) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('transaksi.show', $transaksi->id_transaksi) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <form action="' . route('transaksi.destroy', $transaksi->id_transaksi) . '" method="POST" class="d-inline">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus transaksi ini?\')">
                                        <i class="fas fa-trash action-icon"></i>
                                    </button>
                                </form>
                            </div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('admin.pages.transaksi.index');
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        $orangs = Orang::with(['mitras' => function ($query) {
            $query->wherePivot('status_valid', true);
        }])
        ->whereDoesntHave('mitras', function ($query) {
            $query->where('status_valid', false);
        })
        ->orWhereDoesntHave('mitras')
        ->get();
        $tabungs = Tabung::with('jenisTabung', 'statusTabung')->get(); // Ambil semua tabung tanpa filter status
        $jenisTransaksis = JenisTransaksiDetail::all();
        return view('admin.pages.transaksi.create', compact('orangs', 'tabungs', 'jenisTransaksis'));
    }

    /**
     * Menyimpan transaksi baru ke database dan membuat entri pembayaran.
     * Menambahkan logika untuk memperbarui tanggal_pinjam dan waktu_pinjam di pengembalians untuk transaksi isi ulang.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_orang' => 'required|exists:orangs,id_orang',
            'transaksi_details' => 'required|array|min:1',
            'transaksi_details.*.id_jenis_transaksi_detail' => 'required|exists:jenis_transaksi_details,id_jenis_transaksi_detail',
            'transaksi_details.*.id_tabung' => 'required|exists:tabungs,id_tabung',
            'transaksi_details.*.id_tabung' => [
                'required',
                'exists:tabungs,id_tabung',
                function ($attribute, $value, $fail) use ($request) {
                    $peminjamanTabungs = [];
                    foreach ($request->transaksi_details as $index => $detail) {
                        $jenisTransaksi = JenisTransaksiDetail::find($detail['id_jenis_transaksi_detail']);
                        if ($jenisTransaksi && strtolower($jenisTransaksi->jenis_transaksi) === 'peminjaman') {
                            if (in_array($detail['id_tabung'], $peminjamanTabungs)) {
                                $fail("Tabung pada {$attribute} sudah dipilih untuk peminjaman di detail lain.");
                            }
                            $peminjamanTabungs[] = $detail['id_tabung'];
                        }
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $total_transaksi = 0;
            $now = Carbon::now();
            $statusDipinjam = StatusTabung::where('status_tabung', 'Dipinjam')->firstOrFail();
            $statusTersedia = StatusTabung::where('status_tabung', 'Tersedia')->firstOrFail();

            foreach ($request->transaksi_details as $detail) {
                $jenisTransaksi = JenisTransaksiDetail::find($detail['id_jenis_transaksi_detail']);
                $tabung = Tabung::with('jenisTabung')->find($detail['id_tabung']);
                
                Log::info('Jenis transaksi ditemukan: ' . $jenisTransaksi->jenis_transaksi);

                $harga = 0;
                $jenisTransaksiLower = strtolower($jenisTransaksi->jenis_transaksi);
                if ($jenisTransaksiLower === 'peminjaman') {
                    $harga = $tabung->jenisTabung->harga_pinjam + $tabung->jenisTabung->harga_isi_ulang + $tabung->jenisTabung->nilai_deposit;
                } elseif ($jenisTransaksiLower === 'isi ulang' || $jenisTransaksiLower === 'isi_ulang') {
                    $harga = $tabung->jenisTabung->harga_isi_ulang;
                } else {
                    throw new \Exception('Jenis transaksi tidak valid: ' . $jenisTransaksi->jenis_transaksi);
                }
                
                $total_transaksi += $harga;
                $detail['harga'] = $harga;
            }

            $transaksi = Transaksi::create([
                'id_orang' => $request->id_orang,
                'total_transaksi' => $total_transaksi,
                'status_valid' => true,
                'tanggal_transaksi' => $now->toDateString(),
                'waktu_transaksi' => $now->toTimeString(),
            ]);

            foreach ($request->transaksi_details as $detail) {
                $transaksiDetail = TransaksiDetail::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_tabung' => $detail['id_tabung'],
                    'id_jenis_transaksi_detail' => $detail['id_jenis_transaksi_detail'],
                    'harga' => $detail['harga'],
                ]);

                $tabung = Tabung::find($detail['id_tabung']);
                $jenisTransaksi = JenisTransaksiDetail::find($detail['id_jenis_transaksi_detail']);
                
                if (strtolower($jenisTransaksi->jenis_transaksi) === 'peminjaman') {
                    if ($tabung->id_status_tabung != $statusTersedia->id_status_tabung) {
                        throw new \Exception('Tabung ' . $tabung->kode_tabung . ' tidak tersedia untuk dipinjam.');
                    }
                    $tabung->update(['id_status_tabung' => $statusDipinjam->id_status_tabung]);

                    Pengembalian::create([
                        'id_tabung' => $detail['id_tabung'],
                        'id_transaksi_detail' => $transaksiDetail->id_transaksi_detail,
                        'id_status_tabung' => $statusDipinjam->id_status_tabung,
                        'tanggal_pinjam' => $now,
                        'waktu_pinjam' => $now->toTimeString(),
                        'deposit' => $tabung->jenisTabung->nilai_deposit,
                        'sisa_deposit' => $tabung->jenisTabung->nilai_deposit,
                        'bayar_tagihan' => 0,
                        'total_denda' => 0,
                        'denda_kondisi_tabung' => 0,
                        'jumlah_keterlambatan_bulan' => 0,
                    ]);
                }

                // Tambahan: Perbarui tanggal_pinjam dan waktu_pinjam untuk transaksi isi ulang
                if (in_array(strtolower($jenisTransaksi->jenis_transaksi), ['isi ulang', 'isi_ulang'])) {
                    if (!in_array($tabung->id_status_tabung, [$statusTersedia->id_status_tabung, $statusDipinjam->id_status_tabung])) {
                        throw new \Exception('Tabung ' . $tabung->kode_tabung . ' tidak dapat diisi ulang.');
                    }
                    $pengembalian = Pengembalian::where('id_tabung', $detail['id_tabung'])
                        ->whereNull('tanggal_pengembalian')
                        ->first();
                    if ($pengembalian) {
                        $pengembalian->update([
                            'tanggal_pinjam' => $now->toDateString(),
                            'waktu_pinjam' => $now->toTimeString(),
                        ]);
                        Log::info('Tanggal pinjam untuk pengembalian ID ' . $pengembalian->id_pengembalian . ' diperbarui ke ' . $now->toDateString() . ' karena transaksi isi ulang untuk tabung ' . $tabung->kode_tabung);
                    }
                }
            }

            // Membuat entri pembayaran setelah transaksi berhasil dibuat
            Pembayaran::create([
                'id_orang' => $request->id_orang,
                'total_transaksi' => $total_transaksi,
                'jumlah_pembayaran' => 0,
                'metode_pembayaran' => 'Belum Dibayar',
                'nomor_referensi' => null,
                'tanggal_pembayaran' => $now->toDateString(),
                'waktu_pembayaran' => $now->toTimeString(),
            ]);

            DB::commit();
            return redirect()->route('transaksi.show', $transaksi->id_transaksi)->with('success', 'Transaksi dan entri pembayaran berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi atau pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi atau pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail transaksi.
     */
    public function show($id)
    {
        $transaksi = Transaksi::with(['orang', 'transaksiDetails.jenisTransaksiDetail', 'transaksiDetails.tabung.jenisTabung'])->findOrFail($id);
        return view('admin.pages.transaksi.show', compact('transaksi'));
    }

    /**
     * Menghapus transaksi dari database.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->delete();
            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Ekspor data transaksi ke Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = Transaksi::with(['orang', 'transaksiDetails']);
            
            // Terapkan filter berdasarkan jenis filter
            if ($request->has('filter_type') && !empty($request->filter_type)) {
                if ($request->filter_type === 'specific_date' && $request->has('specific_date') && !empty($request->specific_date)) {
                    $query->whereDate('tanggal_transaksi', $request->specific_date);
                } elseif ($request->filter_type === 'date_range' && $request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                    $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                } elseif ($request->filter_type === 'month' && $request->has('month') && !empty($request->month)) {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $query->whereMonth('tanggal_transaksi', $date->month)
                          ->whereYear('tanggal_transaksi', $date->year);
                }
            }

            $transaksis = $query->get();
            
            if ($transaksis->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data transaksi untuk diekspor.');
            }

            return Excel::download(new class($transaksis) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $transaksis;

                public function __construct($transaksis)
                {
                    $this->transaksis = $transaksis;
                }

                public function collection()
                {
                    return $this->transaksis->map(function ($transaksi, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Pelanggan' => $transaksi->orang ? $transaksi->orang->nama_lengkap : '-',
                            'Total Transaksi' => 'Rp ' . number_format($transaksi->total_transaksi, 2, ',', '.'),
                            'Tanggal Transaksi' => $transaksi->tanggal_transaksi instanceof \Carbon\Carbon 
                                ? $transaksi->tanggal_transaksi->format('Y-m-d')
                                : ($transaksi->tanggal_transaksi ? substr($transaksi->tanggal_transaksi, 0, 10) : '-'),
                            'Waktu Transaksi' => $transaksi->waktu_transaksi ?? '-',
                            'Status' => $transaksi->status_valid ? 'Valid' : 'Batal',
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Pelanggan',
                        'Total Transaksi',
                        'Tanggal Transaksi',
                        'Waktu Transaksi',
                        'Status',
                    ];
                }
            }, 'data_transaksi_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data transaksi ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data transaksi ke Excel: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor data transaksi ke PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Transaksi::with(['orang', 'transaksiDetails']);
            
            // Terapkan filter berdasarkan jenis filter
            if ($request->has('filter_type') && !empty($request->filter_type)) {
                if ($request->filter_type === 'specific_date' && $request->has('specific_date') && !empty($request->specific_date)) {
                    $query->whereDate('tanggal_transaksi', $request->specific_date);
                } elseif ($request->filter_type === 'date_range' && $request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                    $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                } elseif ($request->filter_type === 'month' && $request->has('month') && !empty($request->month)) {
                    $date = Carbon::createFromFormat('Y-m', $request->month);
                    $query->whereMonth('tanggal_transaksi', $date->month)
                          ->whereYear('tanggal_transaksi', $date->year);
                }
            }

            $transaksis = $query->get();
            
            if ($transaksis->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data transaksi untuk diekspor.');
            }

            $pdf = Pdf::loadView('admin.pages.transaksi.transaksi_export', compact('transaksis'));
            return $pdf->download('data_transaksi_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data transaksi ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data transaksi ke PDF: ' . $e->getMessage());
        }
    }
}