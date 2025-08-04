<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StatusTabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StatusTabungController extends Controller
{
    /**
     * Menampilkan daftar status tabung dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = StatusTabung::query();

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where('status_tabung', 'like', '%' . $search . '%');
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('action', function ($statusTabung) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.status_tabung.show', $statusTabung->id_status_tabung) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.status_tabung.edit', $statusTabung->id_status_tabung) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.status_tabung.destroy', $statusTabung->id_status_tabung) . '" method="POST" class="d-inline">
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

            return view('admin.pages.status_tabung.index');
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar status tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat status tabung baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.status_tabung.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah status tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan status tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status_tabung' => 'required|string|max:255|unique:status_tabungs',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            StatusTabung::create([
                'status_tabung' => $request->status_tabung,
            ]);

            return redirect()->route('admin.status_tabung.index')->with('success', 'Status tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan status tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail status tabung.
     */
    public function show($id)
    {
        try {
            $statusTabung = StatusTabung::findOrFail($id);
            return view('admin.pages.status_tabung.show', compact('statusTabung'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail status tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit status tabung.
     */
    public function edit($id)
    {
        try {
            $statusTabung = StatusTabung::findOrFail($id);
            return view('admin.pages.status_tabung.edit', compact('statusTabung'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit status tabung. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui status tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $statusTabung = StatusTabung::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status_tabung' => 'required|string|max:255|unique:status_tabungs,status_tabung,' . $id . ',id_status_tabung',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $statusTabung->update([
                'status_tabung' => $request->status_tabung,
            ]);

            return redirect()->route('admin.status_tabung.index')->with('success', 'Status tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui status tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus status tabung dari database.
     */
    public function destroy($id)
    {
        try {
            $statusTabung = StatusTabung::findOrFail($id);
            $statusTabung->delete();

            return redirect()->route('admin.status_tabung.index')->with('success', 'Status tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus status tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus status tabung. Silakan coba lagi.');
        }
    }

    /**
     * Export data status tabung ke Excel.
     */
    public function exportExcel()
    {
        try {
            $statusTabungs = StatusTabung::all();
            return Excel::download(new class($statusTabungs) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $statusTabungs;

                public function __construct($statusTabungs)
                {
                    $this->statusTabungs = $statusTabungs;
                }

                public function collection()
                {
                    return $this->statusTabungs->map(function ($statusTabung, $index) {
                        return [
                            'No' => $index + 1,
                            'Status Tabung' => $statusTabung->status_tabung,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Status Tabung',
                    ];
                }
            }, 'data_status_tabung_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data status tabung ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data status tabung ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data status tabung ke PDF.
     */
    public function exportPdf()
    {
        try {
            $statusTabungs = StatusTabung::all();
            $pdf = Pdf::loadView('admin.pages.status_tabung.status_tabung_export', compact('statusTabungs'));
            return $pdf->download('data_status_tabung_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data status tabung ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data status tabung ke PDF. Silakan coba lagi.');
        }
    }
}