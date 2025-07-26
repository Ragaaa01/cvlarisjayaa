<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perusahaans = Perusahaan::when($search, function ($query, $search) {
                return $query->where('nama_perusahaan', 'like', "%{$search}%")
                             ->orWhere('alamat_perusahaan', 'like', "%{$search}%");
            })->paginate(15); // Mengubah paginasi menjadi 15 item per halaman
            return view('admin.pages.perusahaan.index', compact('perusahaans', 'search'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar perusahaan. Silakan coba lagi.');
        }
    }

    public function create()
    {
        try {
            return view('admin.pages.perusahaan.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah perusahaan: ' . $e->getMessage());
            return redirect()->route('admin.perusahaan.index')->with('error', 'Gagal memuat form tambah perusahaan. Silakan coba lagi.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            Perusahaan::create($request->only(['nama_perusahaan', 'alamat_perusahaan']));
            DB::commit();

            return redirect()->route('admin.perusahaan.index')
                ->with('success', 'Perusahaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan perusahaan. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        try {
            $perusahaan = Perusahaan::findOrFail($id);
            return view('admin.pages.perusahaan.show', compact('perusahaan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail perusahaan: ' . $e->getMessage());
            return redirect()->route('admin.perusahaan.index')->with('error', 'Gagal memuat detail perusahaan. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        try {
            $perusahaan = Perusahaan::findOrFail($id);
            return view('admin.pages.perusahaan.edit', compact('perusahaan'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit perusahaan: ' . $e->getMessage());
            return redirect()->route('admin.perusahaan.index')->with('error', 'Gagal memuat form edit perusahaan. Silakan coba lagi.');
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $perusahaan = Perusahaan::findOrFail($id);
            $perusahaan->update($request->only(['nama_perusahaan', 'alamat_perusahaan']));
            DB::commit();

            return redirect()->route('admin.perusahaan.index')
                ->with('success', 'Perusahaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui perusahaan. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $perusahaan = Perusahaan::findOrFail($id);
            $perusahaan->delete();
            DB::commit();

            return redirect()->route('admin.perusahaan.index')
                ->with('success', 'Perusahaan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus perusahaan. Silakan coba lagi.');
        }
    }
}