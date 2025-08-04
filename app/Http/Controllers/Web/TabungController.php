<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use App\Models\JenisTabung;
use App\Models\StatusTabung;
use App\Models\Kepemilikan;
use App\Models\Pengembalian;
use App\Models\TransaksiDetail;
use App\Models\Transaksi;
use App\Models\Orang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TabungController extends Controller
{
    /**
     * Menampilkan daftar tabung dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan']);

                // Terapkan filter berdasarkan jenis tabung jika ada dan tidak kosong
                if ($request->has('id_jenis_tabung') && !empty($request->id_jenis_tabung)) {
                    $query->where('id_jenis_tabung', $request->id_jenis_tabung);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('jenis_tabung', function ($tabung) {
                        return $tabung->jenisTabung->nama_jenis;
                    })
                    ->addColumn('status_tabung', function ($tabung) {
                        return $tabung->statusTabung->status_tabung;
                    })
                    ->addColumn('kepemilikan', function ($tabung) {
                        return $tabung->kepemilikan->keterangan_kepemilikan;
                    })
                    ->addColumn('action', function ($tabung) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.tabung.show', $tabung->id_tabung) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.tabung.edit', $tabung->id_tabung) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.tabung.destroy', $tabung->id_tabung) . '" method="POST" class="d-inline">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')">
                                        <i class="fas fa-trash action-icon"></i>
                                    </button>
                                </form>
                            </div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            $jenisTabungs = JenisTabung::all(); // Untuk dropdown filter
            return view('admin.pages.tabung.index', compact('jenisTabungs'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat tabung baru.
     */
    public function create()
    {
        try {
            $jenisTabungs = JenisTabung::all();
            $kepemilikans = Kepemilikan::all();
            return view('admin.pages.tabung.create', compact('jenisTabungs', 'kepemilikans'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kode_tabung' => 'required|string',
                'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
                'id_kepemilikan' => 'required|exists:kepemilikans,id_kepemilikan',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Ambil ID status tabung "Tersedia"
            $statusTersedia = StatusTabung::where('status_tabung', 'Tersedia')->first();
            if (!$statusTersedia) {
                return redirect()->back()->with('error', 'Status "Tersedia" tidak ditemukan di database. Silakan tambahkan terlebih dahulu.')->withInput();
            }

            // Pisahkan kode tabung berdasarkan koma atau baris baru
            $kodeTabungs = array_filter(array_map('trim', preg_split('/[\n,]+/', $request->kode_tabung)));

            // Validasi kode tabung: pastikan tidak kosong dan unik
            if (empty($kodeTabungs)) {
                return redirect()->back()->with('error', 'Kode tabung tidak boleh kosong.')->withInput();
            }

            // Cek apakah ada kode tabung yang sudah ada di database
            $existingKodes = Tabung::whereIn('kode_tabung', $kodeTabungs)->pluck('kode_tabung')->toArray();
            if (!empty($existingKodes)) {
                return redirect()->back()->with('error', 'Kode tabung berikut sudah ada: ' . implode(', ', $existingKodes))->withInput();
            }

            // Simpan setiap kode tabung
            foreach ($kodeTabungs as $kode) {
                if (strlen($kode) > 255) {
                    return redirect()->back()->with('error', "Kode tabung '$kode' melebihi panjang maksimum 255 karakter.")->withInput();
                }

                Tabung::create([
                    'kode_tabung' => $kode,
                    'id_jenis_tabung' => $request->id_jenis_tabung,
                    'id_status_tabung' => $statusTersedia->id_status_tabung,
                    'id_kepemilikan' => $request->id_kepemilikan,
                ]);
            }

            return redirect()->route('admin.tabung.index')->with('success', 'Tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail tabung beserta status peminjaman dan riwayat.
     */
    public function show($id)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan'])->findOrFail($id);

            // Ambil data peminjaman saat ini (jika ada)
            $peminjamanSaatIni = Pengembalian::where('id_tabung', $id)
                ->whereNull('tanggal_pengembalian')
                ->with(['transaksiDetail.transaksi.orang'])
                ->first();

            // Ambil riwayat peminjaman dan pengembalian
            $riwayatPeminjaman = Pengembalian::where('id_tabung', $id)
                ->with(['transaksiDetail.transaksi.orang', 'statusTabung'])
                ->orderBy('tanggal_pinjam', 'desc')
                ->get();

            return view('admin.pages.tabung.show', compact('tabung', 'peminjamanSaatIni', 'riwayatPeminjaman'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit tabung.
     */
    public function edit($id)
    {
        try {
            $tabung = Tabung::findOrFail($id);
            $jenisTabungs = JenisTabung::all();
            $statusTabungs = StatusTabung::all();
            $kepemilikans = Kepemilikan::all();
            return view('admin.pages.tabung.edit', compact('tabung', 'jenisTabungs', 'statusTabungs', 'kepemilikans'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit tabung. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $tabung = Tabung::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kode_tabung' => 'required|string|max:255|unique:tabungs,kode_tabung,' . $id . ',id_tabung',
                'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
                'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
                'id_kepemilikan' => 'required|exists:kepemilikans,id_kepemilikan',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $tabung->update([
                'kode_tabung' => $request->kode_tabung,
                'id_jenis_tabung' => $request->id_jenis_tabung,
                'id_status_tabung' => $request->id_status_tabung,
                'id_kepemilikan' => $request->id_kepemilikan,
            ]);

            return redirect()->route('admin.tabung.index')->with('success', 'Tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus tabung dari database.
     */
    public function destroy($id)
    {
        try {
            $tabung = Tabung::findOrFail($id);
            $tabung->delete();

            return redirect()->route('admin.tabung.index')->with('success', 'Tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus tabung. Silakan coba lagi.');
        }
    }

    /**
     * Export data tabung ke Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan']);
            
            // Terapkan filter berdasarkan jenis tabung jika ada dan tidak kosong
            if ($request->has('id_jenis_tabung') && !empty($request->id_jenis_tabung)) {
                $query->where('id_jenis_tabung', $request->id_jenis_tabung);
            }

            $tabungs = $query->get();
            return Excel::download(new class($tabungs) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $tabungs;

                public function __construct($tabungs)
                {
                    $this->tabungs = $tabungs;
                }

                public function collection()
                {
                    return $this->tabungs->map(function ($tabung, $index) {
                        return [
                            'No' => $index + 1,
                            'Kode Tabung' => $tabung->kode_tabung,
                            'Jenis Tabung' => $tabung->jenisTabung->nama_jenis,
                            'Status Tabung' => $tabung->statusTabung->status_tabung,
                            'Kepemilikan' => $tabung->kepemilikan->keterangan_kepemilikan,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Kode Tabung',
                        'Jenis Tabung',
                        'Status Tabung',
                        'Kepemilikan',
                    ];
                }
            }, 'data_tabung_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data tabung ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data tabung ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data tabung ke PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Tabung::with(['jenisTabung', 'statusTabung', 'kepemilikan']);
            
            // Terapkan filter berdasarkan jenis tabung jika ada dan tidak kosong
            if ($request->has('id_jenis_tabung') && !empty($request->id_jenis_tabung)) {
                $query->where('id_jenis_tabung', $request->id_jenis_tabung);
            }

            $tabungs = $query->get();
            $pdf = Pdf::loadView('admin.pages.tabung.tabung_export', compact('tabungs'));
            return $pdf->download('data_tabung_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data tabung ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data tabung ke PDF. Silakan coba lagi.');
        }
    }
}