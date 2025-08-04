<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Mitra;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MitraController extends Controller
{
    /**
     * Menampilkan daftar mitra dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Mitra::query();

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_mitra', 'like', '%' . $search . '%')
                          ->orWhere('alamat_mitra', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('alamat_mitra', function ($mitra) {
                        return $mitra->alamat_mitra ?? '-';
                    })
                    ->addColumn('verified', function ($mitra) {
                        return $mitra->verified ? 'Terverifikasi' : 'Belum Terverifikasi';
                    })
                    ->addColumn('action', function ($mitra) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.mitra.show', $mitra->id_mitra) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.mitra.edit', $mitra->id_mitra) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.mitra.destroy', $mitra->id_mitra) . '" method="POST" class="d-inline">
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

            return view('admin.pages.mitra.index');
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat data mitra baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.mitra.create');
        } catch (Exception $e) {
            Log::error('Gagal memuat form tambah mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan data mitra baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_mitra' => 'required|string|max:255',
                'alamat_mitra' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Mitra::create([
                'nama_mitra' => $request->nama_mitra,
                'alamat_mitra' => $request->alamat_mitra,
                'verified' => true, // Otomatis terverifikasi
            ]);

            return redirect()->route('admin.mitra.index')->with('success', 'Data mitra berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan data mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data mitra. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail data mitra.
     */
    public function show($id)
    {
        try {
            $mitra = Mitra::findOrFail($id);
            return view('admin.pages.mitra.show', compact('mitra'));
        } catch (Exception $e) {
            Log::error('Gagal memuat detail mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit data mitra.
     */
    public function edit($id)
    {
        try {
            $mitra = Mitra::findOrFail($id);
            return view('admin.pages.mitra.edit', compact('mitra'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form edit mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit mitra. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data mitra di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $mitra = Mitra::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_mitra' => 'required|string|max:255',
                'alamat_mitra' => 'required|string',
                'verified' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $mitra->update($request->only(['nama_mitra', 'alamat_mitra', 'verified']));

            return redirect()->route('admin.mitra.index')->with('success', 'Data mitra berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data mitra. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data mitra dari database.
     */
    public function destroy($id)
    {
        try {
            $mitra = Mitra::findOrFail($id);
            $mitra->delete();

            return redirect()->route('admin.mitra.index')->with('success', 'Data mitra berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data mitra. Silakan coba lagi.');
        }
    }

    /**
     * Export data mitra ke Excel.
     */
    public function exportExcel()
    {
        try {
            $mitras = Mitra::all();
            return Excel::download(new class($mitras) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $mitras;

                public function __construct($mitras)
                {
                    $this->mitras = $mitras;
                }

                public function collection()
                {
                    return $this->mitras->map(function ($mitra, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Mitra' => $mitra->nama_mitra,
                            'Alamat' => $mitra->alamat_mitra ?? '-',
                            'Status Verifikasi' => $mitra->verified ? 'Terverifikasi' : 'Belum Terverifikasi',
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Mitra',
                        'Alamat',
                        'Status Verifikasi',
                    ];
                }
            }, 'data_mitra_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export data mitra ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data mitra ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data mitra ke PDF.
     */
    public function exportPdf()
    {
        try {
            $mitras = Mitra::all();
            $pdf = Pdf::loadView('admin.pages.mitra.mitra_export', compact('mitras'));
            return $pdf->download('data_mitra_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            Log::error('Gagal export data mitra ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data mitra ke PDF. Silakan coba lagi.');
        }
    }
}