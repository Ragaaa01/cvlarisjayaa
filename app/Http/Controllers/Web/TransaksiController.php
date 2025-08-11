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
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Transaksi::with(['orang']);

                // Apply filters
                if ($request->filter_type === 'specific_date' && $request->specific_date) {
                    $query->whereDate('tanggal_transaksi', $request->specific_date);
                } elseif ($request->filter_type === 'date_range' && $request->start_date && $request->end_date) {
                    $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                } elseif ($request->filter_type === 'month' && $request->month) {
                    $query->whereYear('tanggal_transaksi', substr($request->month, 0, 4))
                          ->whereMonth('tanggal_transaksi', substr($request->month, 5, 2));
                } elseif ($request->filter_type === 'belum_lunas') {
                    $query->where(function ($q) {
                        $q->whereDoesntHave('pembayaran')
                          ->orWhereHas('pembayaran', function ($q2) {
                              $q2->whereRaw('jumlah_pembayaran < total_transaksi');
                          });
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_orang', function ($transaksi) {
                        return $transaksi->orang ? $transaksi->orang->nama_lengkap : '-';
                    })
                    ->addColumn('total_transaksi', function ($transaksi) {
                        return $transaksi->total_transaksi;
                    })
                    ->addColumn('status_pembayaran', function ($transaksi) {
                        $pembayaran = Pembayaran::where('id_orang', $transaksi->id_orang)
                            ->where('total_transaksi', $transaksi->total_transaksi)
                            ->where('tanggal_pembayaran', '>=', $transaksi->tanggal_transaksi)
                            ->orderBy('tanggal_pembayaran', 'desc')
                            ->orderBy('waktu_pembayaran', 'desc')
                            ->first();
                        if ($pembayaran) {
                            if ($pembayaran->jumlah_pembayaran >= $pembayaran->total_transaksi) {
                                return 'Lunas';
                            } elseif ($pembayaran->jumlah_pembayaran > 0 || $pembayaran->metode_pembayaran !== 'Belum Dibayar') {
                                return 'Belum Lunas';
                            }
                        }
                        return 'Belum Dibayar';
                    })
                    ->addColumn('action', function ($transaksi) {
                        $pembayaran = Pembayaran::where('id_orang', $transaksi->id_orang)
                            ->where('total_transaksi', $transaksi->total_transaksi)
                            ->where('tanggal_pembayaran', '>=', $transaksi->tanggal_transaksi)
                            ->orderBy('tanggal_pembayaran', 'desc')
                            ->orderBy('waktu_pembayaran', 'desc')
                            ->first();
                        $action = '
                            <div class="action-buttons">
                                <a href="' . route('transaksi.show', $transaksi->id_transaksi) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('transaksi.print', $transaksi->id_transaksi) . '" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf action-icon"></i>
                                </a>';
                        if (!$pembayaran || ($pembayaran->jumlah_pembayaran < $pembayaran->total_transaksi)) {
                            $pembayaranId = $pembayaran ? $pembayaran->id_pembayaran : 0;
                            $action .= '
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#pembayaranModal"
                                    data-id="' . $pembayaranId . '"
                                    data-id_orang="' . $transaksi->id_orang . '"
                                    data-total_transaksi="' . $transaksi->total_transaksi . '"
                                    data-sisa="' . ($pembayaran ? ($pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran) : $transaksi->total_transaksi) . '"
                                    data-id_transaksi="' . $transaksi->id_transaksi . '">
                                    <i class="fas fa-money-bill action-icon"></i>
                                </button>';
                        }
                        $action .= '</div>';
                        return $action;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            $orangs = Orang::all();
            return view('admin.pages.transaksi.index', compact('orangs'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar transaksi. Silakan coba lagi.');
        }
    }

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
        $tabungs = Tabung::with('jenisTabung', 'statusTabung')->get();
        $jenisTransaksis = JenisTransaksiDetail::all();
        return view('admin.pages.transaksi.create', compact('orangs', 'tabungs', 'jenisTransaksis'));
    }

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

     public function show($id_transaksi)
    {
        try {
            $transaksi = Transaksi::with(['orang', 'transaksiDetails', 'transaksiDetails.tabung', 'transaksiDetails.tabung.jenisTabung', 'transaksiDetails.jenisTransaksiDetail'])
                ->findOrFail($id_transaksi);
            
            $pembayaran = Pembayaran::where('id_orang', $transaksi->id_orang)
                ->where('total_transaksi', $transaksi->total_transaksi)
                ->where('tanggal_pembayaran', '>=', $transaksi->tanggal_transaksi)
                ->orderBy('tanggal_pembayaran', 'desc')
                ->orderBy('waktu_pembayaran', 'desc')
                ->first();
                
            Log::info('Memuat halaman transaksi.show untuk id_transaksi: ' . $id_transaksi, [
                'id_transaksi' => $id_transaksi,
                'pembayaran_id' => $pembayaran ? $pembayaran->id_pembayaran : null
            ]);
            
            return view('admin.pages.transaksi.show', compact('transaksi', 'pembayaran'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail transaksi: ' . $e->getMessage());
            return redirect()->route('transaksi.index')
                ->with('error', 'Gagal memuat detail transaksi: ' . $e->getMessage());
        }
    }


    public function print($id)
    {
        try {
            $transaksi = Transaksi::with(['orang', 'transaksiDetails.jenisTransaksiDetail', 'transaksiDetails.tabung.jenisTabung'])->findOrFail($id);
            $pdf = Pdf::loadView('admin.pages.transaksi.transaksi_print', compact('transaksi'));
            return $pdf->download('nota_transaksi_' . $transaksi->id_transaksi . '_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mencetak nota transaksi ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak nota transaksi ke PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = Transaksi::with(['orang', 'transaksiDetails']);
            
            if ($request->has('filter_type') && !empty($request->filter_type)) {
                if ($request->filter_type === 'specific_date' && $request->has('specific_date') && !empty($request->specific_date)) {
                    $query->whereDate('tanggal_transaksi', $request->specific_date);
                } elseif ($request->filter_type === 'date_range' && $request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                    $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                } elseif ($request->filter_type === 'month' && $request->has('month') && !empty($request->month)) {
                    try {
                        $date = Carbon::createFromFormat('Y-m', $request->month);
                        $query->whereMonth('tanggal_transaksi', $date->month)
                              ->whereYear('tanggal_transaksi', $date->year);
                    } catch (\Exception $e) {
                        Log::warning('Format bulan tidak valid untuk export Excel: ' . $request->month);
                    }
                } elseif ($request->filter_type === 'belum_lunas') {
                    $query->leftJoin('pembayarans', function ($join) {
                        $join->on('pembayarans.id_orang', '=', 'transaksis.id_orang')
                             ->on('pembayarans.total_transaksi', '=', 'transaksis.total_transaksi');
                    })
                    ->where(function ($q) {
                        $q->whereNull('pembayarans.id_pembayaran')
                          ->orWhere('pembayarans.metode_pembayaran', 'Belum Dibayar')
                          ->orWhereRaw('COALESCE(pembayarans.jumlah_pembayaran, 0) < transaksis.total_transaksi');
                    })->select('transaksis.*');
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
                    ];
                }
            }, 'data_transaksi_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data transaksi ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data transaksi ke Excel: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = Transaksi::with(['orang', 'transaksiDetails']);
            
            if ($request->has('filter_type') && !empty($request->filter_type)) {
                if ($request->filter_type === 'specific_date' && $request->has('specific_date') && !empty($request->specific_date)) {
                    $query->whereDate('tanggal_transaksi', $request->specific_date);
                } elseif ($request->filter_type === 'date_range' && $request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                    $query->whereBetween('tanggal_transaksi', [$request->start_date, $request->end_date]);
                } elseif ($request->filter_type === 'month' && $request->has('month') && !empty($request->month)) {
                    try {
                        $date = Carbon::createFromFormat('Y-m', $request->month);
                        $query->whereMonth('tanggal_transaksi', $date->month)
                              ->whereYear('tanggal_transaksi', $date->year);
                    } catch (\Exception $e) {
                        Log::warning('Format bulan tidak valid untuk export PDF: ' . $request->month);
                    }
                } elseif ($request->filter_type === 'belum_lunas') {
                    $query->leftJoin('pembayarans', function ($join) {
                        $join->on('pembayarans.id_orang', '=', 'transaksis.id_orang')
                             ->on('pembayarans.total_transaksi', '=', 'transaksis.total_transaksi');
                    })
                    ->where(function ($q) {
                        $q->whereNull('pembayarans.id_pembayaran')
                          ->orWhere('pembayarans.metode_pembayaran', 'Belum Dibayar')
                          ->orWhereRaw('COALESCE(pembayarans.jumlah_pembayaran, 0) < transaksis.total_transaksi');
                    })->select('transaksis.*');
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