<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JenisTransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class JenisTransaksiController extends Controller
{
    /**
     * Menampilkan daftar jenis transaksi detail dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = JenisTransaksiDetail::query();

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('action', function ($jenisTransaksi) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.jenis_transaksi.show', $jenisTransaksi->id_jenis_transaksi_detail) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.jenis_transaksi.edit', $jenisTransaksi->id_jenis_transaksi_detail) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.jenis_transaksi.destroy', $jenisTransaksi->id_jenis_transaksi_detail) . '" method="POST" class="d-inline">
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

            return view('admin.pages.jenis_transaksi.index');
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar jenis transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat jenis transaksi baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.jenis_transaksi.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah jenis transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan jenis transaksi baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jenis_transaksi' => 'required|string|max:255|unique:jenis_transaksi_details,jenis_transaksi',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            JenisTransaksiDetail::create([
                'jenis_transaksi' => $request->jenis_transaksi,
            ]);

            return redirect()->route('admin.jenis_transaksi.index')->with('success', 'Jenis transaksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan jenis transaksi. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail jenis transaksi.
     */
    public function show($id_jenis_transaksi_detail)
    {
        try {
            $jenisTransaksi = JenisTransaksiDetail::findOrFail($id_jenis_transaksi_detail);
            return view('admin.pages.jenis_transaksi.show', compact('jenisTransaksi'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail jenis transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit jenis transaksi.
     */
    public function edit($id_jenis_transaksi_detail)
    {
        try {
            $jenisTransaksi = JenisTransaksiDetail::findOrFail($id_jenis_transaksi_detail);
            return view('admin.pages.jenis_transaksi.edit', compact('jenisTransaksi'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit jenis transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui jenis transaksi di database.
     */
    public function update(Request $request, $id_jenis_transaksi_detail)
    {
        try {
            $jenisTransaksi = JenisTransaksiDetail::findOrFail($id_jenis_transaksi_detail);

            $validator = Validator::make($request->all(), [
                'jenis_transaksi' => 'required|string|max:255|unique:jenis_transaksi_details,jenis_transaksi,' . $id_jenis_transaksi_detail . ',id_jenis_transaksi_detail',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $jenisTransaksi->update([
                'jenis_transaksi' => $request->jenis_transaksi,
            ]);

            return redirect()->route('admin.jenis_transaksi.index')->with('success', 'Jenis transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui jenis transaksi. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus jenis transaksi dari database.
     */
    public function destroy($id_jenis_transaksi_detail)
    {
        try {
            $jenisTransaksi = JenisTransaksiDetail::findOrFail($id_jenis_transaksi_detail);
            $jenisTransaksi->delete();

            return redirect()->route('admin.jenis_transaksi.index')->with('success', 'Jenis transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus jenis transaksi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus jenis transaksi. Silakan coba lagi.');
        }
    }

    /**
     * Export data jenis transaksi ke Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = JenisTransaksiDetail::query();
            
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('jenis_transaksi', 'like', "%{$search}%");
            }

            $jenisTransaksis = $query->get();
            return Excel::download(new class($jenisTransaksis) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $jenisTransaksis;

                public function __construct($jenisTransaksis)
                {
                    $this->jenisTransaksis = $jenisTransaksis;
                }

                public function collection()
                {
                    return $this->jenisTransaksis->map(function ($jenisTransaksi, $index) {
                        return [
                            'No' => $index + 1,
                            'Jenis Transaksi' => $jenisTransaksi->jenis_transaksi,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Jenis Transaksi',
                    ];
                }
            }, 'data_jenis_transaksi_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data jenis transaksi ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data jenis transaksi ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data jenis transaksi ke PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = JenisTransaksiDetail::query();
            
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('jenis_transaksi', 'like', "%{$search}%");
            }

            $jenisTransaksis = $query->get();
            $pdf = Pdf::loadView('admin.pages.jenis_transaksi.jenis_transaksi_export', compact('jenisTransaksis'));
            return $pdf->download('data_jenis_transaksi_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data jenis transaksi ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data jenis transaksi ke PDF. Silakan coba lagi.');
        }
    }
}