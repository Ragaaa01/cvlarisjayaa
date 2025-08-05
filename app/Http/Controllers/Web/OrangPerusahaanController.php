<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orang;
use App\Models\Perusahaan;
use App\Models\OrangPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrangPerusahaanController extends Controller
{
    public function index()
    {
        $orangPerusahaans = OrangPerusahaan::with(['orang', 'perusahaan'])->get();
        return view('admin.pages.orang_perusahaan.index', compact('orangPerusahaans'));
    }

    public function create()
    {
        // Ambil semua data orang yang belum terkait dengan perusahaan
        $orangs = Orang::whereDoesntHave('perusahaan')->get();
        
        $perusahaans = Perusahaan::all();
        $statuses = ['Pemilik', 'Karyawan', 'PIC'];
        return view('admin.pages.orang_perusahaan.create', compact('orangs', 'perusahaans', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orang' => 'required|exists:orangs,id_orang',
            'id_perusahaan' => 'required|exists:perusahaans,id_perusahaan',
            'status' => 'required|in:Pemilik,Karyawan,PIC',
        ]);

        try {
            DB::beginTransaction();
            OrangPerusahaan::create([
                'id_orang' => $request->id_orang,
                'id_perusahaan' => $request->id_perusahaan,
                'status' => $request->status,
            ]);
            DB::commit();

            return redirect()->route('admin.orang_perusahaan.index')
                ->with('success', 'Relasi orang-perusahaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan relasi orang-perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan relasi. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        $orangPerusahaan = OrangPerusahaan::with(['orang', 'perusahaan'])->findOrFail($id);
        return view('admin.pages.orang_perusahaan.show', compact('orangPerusahaan'));
    }

    public function edit($id)
    {
        $orangPerusahaan = OrangPerusahaan::findOrFail($id);
        
        // Ambil semua data orang, termasuk orang saat ini yang sedang diedit
        $orangs = Orang::where(function ($query) use ($orangPerusahaan) {
            $query->whereDoesntHave('perusahaan')
                  ->orWhere('id_orang', $orangPerusahaan->id_orang);
        })->get();

        $perusahaans = Perusahaan::all();
        $statuses = ['Pemilik', 'Karyawan', 'PIC'];
        return view('admin.pages.orang_perusahaan.edit', compact('orangPerusahaan', 'orangs', 'perusahaans', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orang' => 'required|exists:orangs,id_orang',
            'id_perusahaan' => 'required|exists:perusahaans,id_perusahaan',
            'status' => 'required|in:Pemilik,Karyawan,PIC',
        ]);

        try {
            DB::beginTransaction();
            $orangPerusahaan = OrangPerusahaan::findOrFail($id);
            $orangPerusahaan->update([
                'id_orang' => $request->id_orang,
                'id_perusahaan' => $request->id_perusahaan,
                'status' => $request->status,
            ]);
            DB::commit();

            return redirect()->route('admin.orang_perusahaan.index')
                ->with('success', 'Relasi orang-perusahaan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui relasi orang-perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui relasi. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $orangPerusahaan = OrangPerusahaan::findOrFail($id);
            $orangPerusahaan->delete();
            DB::commit();

            return redirect()->route('admin.orang_perusahaan.index')
                ->with('success', 'Relasi orang-perusahaan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus relasi orang-perusahaan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus relasi. Silakan coba lagi.');
        }
    }
}