<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class JenisTabungController extends Controller
{
    /**
     * Menampilkan daftar jenis tabung dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = JenisTabung::query();

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where('nama_jenis', 'like', '%' . $search . '%');
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('harga_pinjam', function ($jenisTabung) {
                        return number_format($jenisTabung->harga_pinjam, 2, ',', '.');
                    })
                    ->addColumn('harga_isi_ulang', function ($jenisTabung) {
                        return number_format($jenisTabung->harga_isi_ulang, 2, ',', '.');
                    })
                    ->addColumn('nilai_deposit', function ($jenisTabung) {
                        return number_format($jenisTabung->nilai_deposit, 2, ',', '.');
                    })
                    ->addColumn('action', function ($jenisTabung) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.jenis_tabung.show', $jenisTabung->id_jenis_tabung) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.jenis_tabung.edit', $jenisTabung->id_jenis_tabung) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.jenis_tabung.destroy', $jenisTabung->id_jenis_tabung) . '" method="POST" class="d-inline">
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

            return view('admin.pages.jenis_tabung.index');
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar jenis tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat data jenis tabung baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.jenis_tabung.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah jenis tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan data jenis tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs,nama_jenis',
                'harga_pinjam_value' => 'required|numeric|min:0',
                'harga_isi_ulang_value' => 'required|numeric|min:0',
                'nilai_deposit_value' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            JenisTabung::create([
                'nama_jenis' => $request->nama_jenis,
                'harga_pinjam' => $request->harga_pinjam_value,
                'harga_isi_ulang' => $request->harga_isi_ulang_value,
                'nilai_deposit' => $request->nilai_deposit_value,
            ]);

            return redirect()->route('admin.jenis_tabung.index')->with('success', 'Data jenis tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data jenis tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail data jenis tabung.
     */
    public function show($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            return view('admin.pages.jenis_tabung.show', compact('jenisTabung'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail jenis tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit data jenis tabung.
     */
    public function edit($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            return view('admin.pages.jenis_tabung.edit', compact('jenisTabung'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit jenis tabung. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data jenis tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs,nama_jenis,' . $id . ',id_jenis_tabung',
                'harga_pinjam_value' => 'required|numeric|min:0',
                'harga_isi_ulang_value' => 'required|numeric|min:0',
                'nilai_deposit_value' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $jenisTabung->update([
                'nama_jenis' => $request->nama_jenis,
                'harga_pinjam' => $request->harga_pinjam_value,
                'harga_isi_ulang' => $request->harga_isi_ulang_value,
                'nilai_deposit' => $request->nilai_deposit_value,
            ]);

            return redirect()->route('admin.jenis_tabung.index')->with('success', 'Data jenis tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data jenis tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data jenis tabung dari database.
     */
    public function destroy($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            $jenisTabung->delete();

            return redirect()->route('admin.jenis_tabung.index')->with('success', 'Data jenis tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus data jenis tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data jenis tabung. Silakan coba lagi.');
        }
    }

    /**
     * Export data jenis tabung ke Excel.
     */
    public function exportExcel()
    {
        try {
            $jenisTabungs = JenisTabung::all();
            return Excel::download(new class($jenisTabungs) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $jenisTabungs;

                public function __construct($jenisTabungs)
                {
                    $this->jenisTabungs = $jenisTabungs;
                }

                public function collection()
                {
                    return $this->jenisTabungs->map(function ($jenisTabung, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Jenis' => $jenisTabung->nama_jenis,
                            'Harga Pinjam' => number_format($jenisTabung->harga_pinjam, 2, ',', '.'),
                            'Harga Isi Ulang' => number_format($jenisTabung->harga_isi_ulang, 2, ',', '.'),
                            'Nilai Deposit' => number_format($jenisTabung->nilai_deposit, 2, ',', '.'),
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Jenis',
                        'Harga Pinjam (Rp)',
                        'Harga Isi Ulang (Rp)',
                        'Nilai Deposit (Rp)',
                    ];
                }
            }, 'data_jenis_tabung_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data jenis tabung ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data jenis tabung ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data jenis tabung ke PDF.
     */
    public function exportPdf()
    {
        try {
            $jenisTabungs = JenisTabung::all();
            $pdf = Pdf::loadView('admin.pages.jenis_tabung.jenis_tabung_export', compact('jenisTabungs'));
            return $pdf->download('data_jenis_tabung_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data jenis tabung ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data jenis tabung ke PDF. Silakan coba lagi.');
        }
    }
}