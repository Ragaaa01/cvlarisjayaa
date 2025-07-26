<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JenisTabung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JenisTabungController extends Controller
{
    /**
     * Menampilkan daftar jenis tabung.
     */
    public function index()
    {
        try {
            $jenisTabungs = JenisTabung::all();
            return view('admin.pages.jenis_tabung.index', compact('jenisTabungs'));
        } catch (\Exception $e) {
            Log::error('Error saat mengambil data jenis tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil data jenis tabung.');
        }
    }

    /**
     * Menampilkan form untuk membuat jenis tabung baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.jenis_tabung.create');
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan form create jenis tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan form.');
        }
    }

    /**
     * Menyimpan jenis tabung baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs',
                'harga_pinjam' => 'required|numeric|min:0',
                'harga_isi_ulang' => 'required|numeric|min:0',
                'nilai_deposit' => 'required|numeric|min:0',
            ]);

            JenisTabung::create($validated);

            return redirect()->route('admin.jenis_tabung.index')
                           ->with('success', 'Jenis tabung berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan jenis tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan jenis tabung.');
        }
    }

    /**
     * Menampilkan detail jenis tabung.
     */
    public function show($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            return view('admin.pages.jenis_tabung.show', compact('jenisTabung'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan detail jenis tabung: ' . $e->getMessage());
            return back()->with('error', 'Jenis tabung tidak ditemukan.');
        }
    }

    /**
     * Menampilkan form untuk mengedit jenis tabung.
     */
    public function edit($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            return view('admin.pages.jenis_tabung.edit', compact('jenisTabung'));
        } catch (\Exception $e) {
            Log::error('Error saat menampilkan form edit jenis tabung: ' . $e->getMessage());
            return back()->with('error', 'Jenis tabung tidak ditemukan.');
        }
    }

    /**
     * Memperbarui data jenis tabung di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);

            $validated = $request->validate([
                'nama_jenis' => 'required|string|max:255|unique:jenis_tabungs,nama_jenis,' . $id . ',id_jenis_tabung',
                'harga_pinjam' => 'required|numeric|min:0',
                'harga_isi_ulang' => 'required|numeric|min:0',
                'nilai_deposit' => 'required|numeric|min:0',
            ]);

            $jenisTabung->update($validated);

            return redirect()->route('admin.jenis_tabung.index')
                           ->with('success', 'Jenis tabung berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui jenis tabung: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui jenis tabung.');
        }
    }

    /**
     * Menghapus jenis tabung dari database.
     */
    public function destroy($id)
    {
        try {
            $jenisTabung = JenisTabung::findOrFail($id);
            $jenisTabung->delete();

            return redirect()->route('admin.jenis_tabung.index')
                           ->with('success', 'Jenis tabung berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus jenis tabung: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus jenis tabung.');
        }
    }
}