<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Orang;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrangController extends Controller
{
    /**
     * Menampilkan daftar orang dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Orang::query();

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', '%' . $search . '%')
                          ->orWhere('nik', 'like', '%' . $search . '%')
                          ->orWhere('no_telepon', 'like', '%' . $search . '%')
                          ->orWhere('alamat', 'like', '%' . $search . '%');
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nik', function ($orang) {
                        return $orang->nik ?? '-';
                    })
                    ->addColumn('action', function ($orang) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.orang.show', $orang->id_orang) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.orang.edit', $orang->id_orang) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.orang.destroy', $orang->id_orang) . '" method="POST" class="d-inline">
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

            return view('admin.pages.orang.index');
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar orang. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat data orang baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.orang.create');
        } catch (Exception $e) {
            Log::error('Gagal memuat form tambah orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah orang. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan data orang baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'nullable|string|max:16|unique:orangs,nik',
                'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon',
                'alamat' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Orang::create($request->only(['nama_lengkap', 'nik', 'no_telepon', 'alamat']));

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan data orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data orang. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail data orang.
     */
    public function show($id)
    {
        try {
            $orang = Orang::findOrFail($id);
            return view('admin.pages.orang.show', compact('orang'));
        } catch (Exception $e) {
            Log::error('Gagal memuat detail orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail orang. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit data orang.
     */
    public function edit($id)
    {
        try {
            $orang = Orang::findOrFail($id);
            return view('admin.pages.orang.edit', compact('orang'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form edit orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit orang. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data orang di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $orang = Orang::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'nullable|string|max:16|unique:orangs,nik,' . $id . ',id_orang',
                'no_telepon' => 'required|string|max:15|unique:orangs,no_telepon,' . $id . ',id_orang',
                'alamat' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $orang->update($request->only(['nama_lengkap', 'nik', 'no_telepon', 'alamat']));

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data orang. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data orang dari database.
     */
    public function destroy($id)
    {
        try {
            $orang = Orang::findOrFail($id);
            $orang->delete();

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data orang: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data orang. Silakan coba lagi.');
        }
    }

    /**
     * Export data orang ke Excel.
     */
    public function exportExcel()
    {
        try {
            $orangs = Orang::all();
            return Excel::download(new class($orangs) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $orangs;

                public function __construct($orangs)
                {
                    $this->orangs = $orangs;
                }

                public function collection()
                {
                    return $this->orangs->map(function ($orang, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Lengkap' => $orang->nama_lengkap,
                            'NIK' => $orang->nik ?? '-',
                            'No Telepon' => $orang->no_telepon,
                            'Alamat' => $orang->alamat,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Lengkap',
                        'NIK',
                        'No Telepon',
                        'Alamat',
                    ];
                }
            }, 'data_orang_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export data orang ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data orang ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data orang ke PDF.
     */
    public function exportPdf()
    {
        try {
            $orangs = Orang::all();
            $pdf = Pdf::loadView('admin.pages.orang.orang_export', compact('orangs'));
            return $pdf->download('data_orang_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            Log::error('Gagal export data orang ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data orang ke PDF. Silakan coba lagi.');
        }
    }
}