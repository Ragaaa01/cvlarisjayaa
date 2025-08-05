<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kepemilikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class KepemilikanTabungController extends Controller
{
    /**
     * Menampilkan daftar kepemilikan tabung.
     */
    public function index(Request $request)
    {
        try {
            $kepemilikans = Kepemilikan::paginate(10);
            return view('admin.pages.kepemilikan_tabung.index', compact('kepemilikans'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar kepemilikan tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat kepemilikan tabung baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.kepemilikan_tabung.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah kepemilikan tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan kepemilikan tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'keterangan_kepemilikan' => 'required|string|max:255|unique:kepemilikans,keterangan_kepemilikan',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Kepemilikan::create([
                'keterangan_kepemilikan' => $request->keterangan_kepemilikan,
            ]);

            return redirect()->route('admin.kepemilikan_tabung.index')->with('success', 'Kepemilikan tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan kepemilikan tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail kepemilikan tabung.
     */
    public function show($id_kepemilikan)
    {
        try {
            $kepemilikan = Kepemilikan::findOrFail($id_kepemilikan);
            return view('admin.pages.kepemilikan_tabung.show', compact('kepemilikan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail kepemilikan tabung. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit kepemilikan tabung.
     */
    public function edit($id_kepemilikan)
    {
        try {
            $kepemilikan = Kepemilikan::findOrFail($id_kepemilikan);
            return view('admin.pages.kepemilikan_tabung.edit', compact('kepemilikan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit kepemilikan tabung. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui kepemilikan tabung di database.
     */
    public function update(Request $request, $id_kepemilikan)
    {
        try {
            $kepemilikan = Kepemilikan::findOrFail($id_kepemilikan);

            $validator = Validator::make($request->all(), [
                'keterangan_kepemilikan' => 'required|string|max:255|unique:kepemilikans,keterangan_kepemilikan,' . $id_kepemilikan . ',id_kepemilikan',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $kepemilikan->update([
                'keterangan_kepemilikan' => $request->keterangan_kepemilikan,
            ]);

            return redirect()->route('admin.kepemilikan_tabung.index')->with('success', 'Kepemilikan tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui kepemilikan tabung. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus kepemilikan tabung dari database.
     */
    public function destroy($id_kepemilikan)
    {
        try {
            $kepemilikan = Kepemilikan::findOrFail($id_kepemilikan);
            $kepemilikan->delete();

            return redirect()->route('admin.kepemilikan_tabung.index')->with('success', 'Kepemilikan tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus kepemilikan tabung: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kepemilikan tabung. Silakan coba lagi.');
        }
    }

    /**
     * Export data kepemilikan tabung ke Excel.
     */
    public function exportExcel()
    {
        try {
            $kepemilikans = Kepemilikan::all();
            return Excel::download(new class($kepemilikans) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $kepemilikans;

                public function __construct($kepemilikans)
                {
                    $this->kepemilikans = $kepemilikans;
                }

                public function collection()
                {
                    return $this->kepemilikans->map(function ($kepemilikan, $index) {
                        return [
                            'No' => $index + 1,
                            'Keterangan Kepemilikan' => $kepemilikan->keterangan_kepemilikan,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Keterangan Kepemilikan',
                    ];
                }
            }, 'data_kepemilikan_tabung_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal export data kepemilikan tabung ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data kepemilikan tabung ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data kepemilikan tabung ke PDF.
     */
    public function exportPdf()
    {
        try {
            $kepemilikans = Kepemilikan::all();
            $pdf = Pdf::loadView('admin.pages.kepemilikan_tabung.kepemilikan_tabung_export', compact('kepemilikans'));
            return $pdf->download('data_kepemilikan_tabung_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal export data kepemilikan tabung ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data kepemilikan tabung ke PDF. Silakan coba lagi.');
        }
    }
}