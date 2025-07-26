<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StatusTabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatusTabungController extends Controller
{
    /**
     * Menampilkan daftar status tabung.
     */
    public function index()
    {
        try {
            $statusTabungs = StatusTabung::all();
            return view('admin.pages.status_tabung.index', compact('statusTabungs'));
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data status tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil data status tabung.');
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
            Log::error('Error saat menampilkan form create status tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan form.');
        }
    }

    /**
     * Menyimpan status tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'status_tabung' => 'required|string|max:255|unique:status_tabungs',
            ]);

            StatusTabung::create($validated);

            return redirect()->route('admin.status_tabung.index')
                           ->with('success', 'Status tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan status tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan status tabung.');
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
            Log::error('Error saat menampilkan detail status tabung: ' . $e->getMessage());
            return back()->with('error', 'Status tabung tidak ditemukan.');
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
            Log::error('Error saat menampilkan form edit status tabung: ' . $e->getMessage());
            return back()->with('error', 'Status tabung tidak ditemukan.');
        }
    }

    /**
     * Memperbarui data status tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $statusTabung = StatusTabung::findOrFail($id);

            $validated = $request->validate([
                'status_tabung' => 'required|string|max:255|unique:status_tabungs,status_tabung,' . $id . ',id_status_tabung',
            ]);

            $statusTabung->update($validated);

            return redirect()->route('admin.status_tabung.index')
                           ->with('success', 'Status tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui status tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui status tabung.');
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

            return redirect()->route('admin.status_tabung.index')
                           ->with('success', 'Status tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus status tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus status tabung.');
        }
    }
}