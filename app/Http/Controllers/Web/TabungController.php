<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tabung;
use App\Models\JenisTabung;
use App\Models\StatusTabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TabungController extends Controller
{
    /**
     * Menampilkan daftar tabung.
     */
    public function index()
    {
        try {
            $tabungs = Tabung::with(['jenisTabung', 'statusTabung'])->get();
            return view('admin.pages.tabung.index', compact('tabungs'));
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil data tabung.');
        }
    }

    /**
     * Menampilkan form untuk membuat tabung baru.
     */
    public function create()
    {
        try {
            $jenisTabungs = JenisTabung::all();
            $statusTabungs = StatusTabung::all();
            return view('admin.pages.tabung.create', compact('jenisTabungs', 'statusTabungs'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan form create tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan form.');
        }
    }

    /**
     * Menyimpan tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_tabung' => 'required|string|max:255|unique:tabungs',
                'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
                'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
            ]);

            Tabung::create($validated);

            return redirect()->route('admin.tabung.index')
                           ->with('success', 'Tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan tabung.');
        }
    }

    /**
     * Menampilkan detail tabung.
     */
    public function show($id)
    {
        try {
            $tabung = Tabung::with(['jenisTabung', 'statusTabung'])->findOrFail($id);
            return view('admin.pages.tabung.show', compact('tabung'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan detail tabung: ' . $e->getMessage());
            return back()->with('error', 'Tabung tidak ditemukan.');
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
            return view('admin.pages.tabung.edit', compact('tabung', 'jenisTabungs', 'statusTabungs'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan form edit tabung: ' . $e->getMessage());
            return back()->with('error', 'Tabung tidak ditemukan.');
        }
    }

    /**
     * Memperbarui data tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $tabung = Tabung::findOrFail($id);

            $validated = $request->validate([
                'kode_tabung' => 'required|string|max:255|unique:tabungs,kode_tabung,' . $id . ',id_tabung',
                'id_jenis_tabung' => 'required|exists:jenis_tabungs,id_jenis_tabung',
                'id_status_tabung' => 'required|exists:status_tabungs,id_status_tabung',
            ]);

            $tabung->update($validated);

            return redirect()->route('admin.tabung.index')
                           ->with('success', 'Tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui tabung.');
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

            return redirect()->route('admin.tabung.index')
                           ->with('success', 'Tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus tabung.');
        }
    }
}