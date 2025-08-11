<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Orang;
use App\Models\Mitra;
use App\Models\OrangMitra;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrangMitraController extends Controller
{
    /**
     * Menampilkan daftar orang mitra dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = OrangMitra::with(['orang', 'mitra']);

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('orang', function ($q) use ($search) {
                            $q->where('nama_lengkap', 'like', '%' . $search . '%');
                        })->orWhereHas('mitra', function ($q) use ($search) {
                            $q->where('nama_mitra', 'like', '%' . $search . '%');
                        });
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_orang', function ($orangMitra) {
                        return $orangMitra->orang->nama_lengkap;
                    })
                    ->addColumn('nama_mitra', function ($orangMitra) {
                        return $orangMitra->mitra->nama_mitra;
                    })
                    ->addColumn('status_valid', function ($orangMitra) {
                        return $orangMitra->status_valid ? 'Valid' : 'Tidak Valid';
                    })
                    ->addColumn('action', function ($orangMitra) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.orang_mitra.show', $orangMitra->id_orang_mitra) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.orang_mitra.edit', $orangMitra->id_orang_mitra) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.orang_mitra.destroy', $orangMitra->id_orang_mitra) . '" method="POST" class="d-inline">
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

            return view('admin.pages.orang_mitra.index');
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar orang mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat data orang mitra baru.
     */
    public function create()
    {
        try {
            // Ambil orang yang belum memiliki relasi di tabel orang_mitras
            $usedOrangIds = OrangMitra::pluck('id_orang');
            $orangs = Orang::whereNotIn('id_orang', $usedOrangIds)->get();
            $mitras = Mitra::all();
            return view('admin.pages.orang_mitra.create', compact('orangs', 'mitras'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form tambah orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah orang mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan data orang mitra baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_orang' => 'required|exists:orangs,id_orang',
                'id_mitra' => 'required|exists:mitras,id_mitra',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Cek apakah relasi sudah ada
            $exists = OrangMitra::where('id_orang', $request->id_orang)
                                ->where('id_mitra', $request->id_mitra)
                                ->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Relasi orang dan mitra sudah ada.')->withInput();
            }

            // Cek apakah mitra sudah memiliki orang dengan status_valid = true
            $hasValidOrang = OrangMitra::where('id_mitra', $request->id_mitra)
                                       ->where('status_valid', true)
                                       ->exists();

            // Tentukan status_valid dan pesan sukses
            $statusValid = !$hasValidOrang;
            $statusMessage = $statusValid
                ? 'Berhasil Dibuat Status Valid karena mitra belum memiliki orang dengan status valid.'
                : 'Berhasil Dibuat Status Tidak Valid karena mitra sudah memiliki orang dengan status valid.';

            OrangMitra::create([
                'id_orang' => $request->id_orang,
                'id_mitra' => $request->id_mitra,
                'status_valid' => $statusValid,
            ]);

            return redirect()->route('admin.orang_mitra.index')->with('success', $statusMessage);
        } catch (Exception $e) {
            Log::error('Gagal menyimpan data orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data orang mitra. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail data orang mitra.
     */
    public function show($id)
    {
        try {
            $orangMitra = OrangMitra::with(['orang', 'mitra'])->findOrFail($id);
            return view('admin.pages.orang_mitra.show', compact('orangMitra'));
        } catch (Exception $e) {
            Log::error('Gagal memuat detail orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail orang mitra. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit data orang mitra.
     */
    public function edit($id)
    {
        try {
            $orangMitra = OrangMitra::findOrFail($id);
            $orangs = Orang::all();
            $mitras = Mitra::all();
            $hasValidOrang = OrangMitra::where('id_mitra', $orangMitra->id_mitra)
                                       ->where('status_valid', true)
                                       ->where('id_orang_mitra', '!=', $id)
                                       ->exists();
            return view('admin.pages.orang_mitra.edit', compact('orangMitra', 'orangs', 'mitras', 'hasValidOrang'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form edit orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit orang mitra. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data orang mitra di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $orangMitra = OrangMitra::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'id_orang' => 'required|exists:orangs,id_orang',
                'id_mitra' => 'required|exists:mitras,id_mitra',
                'status_valid' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Cek apakah relasi sudah ada (kecuali untuk ID yang sedang diedit)
            $exists = OrangMitra::where('id_orang', $request->id_orang)
                                ->where('id_mitra', $request->id_mitra)
                                ->where('id_orang_mitra', '!=', $id)
                                ->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Relasi orang dan mitra sudah ada.')->withInput();
            }

            // Jika status_valid diubah menjadi true, pastikan tidak ada orang lain yang valid untuk mitra ini
            if ($request->status_valid) {
                $hasValidOrang = OrangMitra::where('id_mitra', $request->id_mitra)
                                           ->where('status_valid', true)
                                           ->where('id_orang_mitra', '!=', $id)
                                           ->exists();
                if ($hasValidOrang) {
                    return redirect()->back()->with('error', 'Mitra ini sudah memiliki orang dengan status valid.')->withInput();
                }
            }

            $orangMitra->update([
                'id_orang' => $request->id_orang,
                'id_mitra' => $request->id_mitra,
                'status_valid' => $request->status_valid,
            ]);

            return redirect()->route('admin.orang_mitra.index')->with('success', 'Data orang mitra berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data orang mitra. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data orang mitra dari database.
     */
    public function destroy($id)
    {
        try {
            $orangMitra = OrangMitra::findOrFail($id);
            $orangMitra->delete();

            return redirect()->route('admin.orang_mitra.index')->with('success', 'Data orang mitra berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data orang mitra: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data orang mitra. Silakan coba lagi.');
        }
    }

    /**
     * Export data orang mitra ke Excel.
     */
    public function exportExcel()
    {
        try {
            $orangMitras = OrangMitra::with(['orang', 'mitra'])->get();
            return Excel::download(new class($orangMitras) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $orangMitras;

                public function __construct($orangMitras)
                {
                    $this->orangMitras = $orangMitras;
                }

                public function collection()
                {
                    return $this->orangMitras->map(function ($orangMitra, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Orang' => $orangMitra->orang->nama_lengkap,
                            'Nama Mitra' => $orangMitra->mitra->nama_mitra,
                            'Status Valid' => $orangMitra->status_valid ? 'Valid' : 'Tidak Valid',
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Orang',
                        'Nama Mitra',
                        'Status Valid',
                    ];
                }
            }, 'data_orang_mitra_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export data orang mitra ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data orang mitra ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data orang mitra ke PDF.
     */
    public function exportPdf()
    {
        try {
            $orangMitras = OrangMitra::with(['orang', 'mitra'])->get();
            $pdf = Pdf::loadView('admin.pages.orang_mitra.orang_mitra_export', compact('orangMitras'));
            return $pdf->download('data_orang_mitra_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            Log::error('Gagal export data orang mitra ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data orang mitra ke PDF. Silakan coba lagi.');
        }
    }
}