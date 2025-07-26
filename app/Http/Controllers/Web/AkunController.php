<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Role;
use App\Models\Orang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class AkunController extends Controller
{
    public function index()
    {
        try {
            $akuns = Akun::with(['role', 'orang'])->get();
            return view('admin.pages.akun.index', compact('akuns'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data akun: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $roles = Role::whereIn('nama_role', ['Pelanggan', 'Karyawan'])->get();
            // Hanya ambil orang yang belum terkait dengan akun (id_orang tidak ada di tabel akuns)
            $orangs = Orang::whereNotIn('id_orang', Akun::whereNotNull('id_orang')->pluck('id_orang'))->get();
            return view('admin.pages.akun.create', compact('roles', 'orangs'));
        } catch (Exception $e) {
            return redirect()->route('admin.akun.index')->with('error', 'Gagal memuat form pembuatan akun: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:akuns,email',
                'password' => 'required|min:6',
                'id_role' => 'required|exists:roles,id_role',
                'id_orang' => 'nullable|exists:orangs,id_orang',
                'status_aktif' => 'required|boolean',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Email tidak valid.',
                'email.unique' => 'Email sudah digunakan.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'id_role.required' => 'Pilih role untuk akun.',
                'id_role.exists' => 'Role yang dipilih tidak valid.',
                'id_orang.exists' => 'Orang yang dipilih tidak valid.',
                'status_aktif.required' => 'Status aktif wajib dipilih.',
            ]);

            Akun::create([
                'id_role' => $request->id_role,
                'id_orang' => $request->id_orang,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status_aktif' => $request->status_aktif,
            ]);

            return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil dibuat.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat akun: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Akun $id)
    {
        try {
            $akun = $id->load(['role', 'orang']);
            return view('admin.pages.akun.show', compact('akun'));
        } catch (Exception $e) {
            return redirect()->route('admin.akun.index')->with('error', 'Gagal memuat detail akun: ' . $e->getMessage());
        }
    }

    public function edit(Akun $id)
    {
        try {
            $akun = $id;
            $roles = Role::whereIn('nama_role', ['Pelanggan', 'Karyawan'])->get();
            // Ambil orang yang belum terkait dengan akun lain, kecuali orang yang terkait dengan akun saat ini
            $orangs = Orang::whereNotIn('id_orang', Akun::whereNotNull('id_orang')
                ->where('id_akun', '!=', $akun->id_akun)
                ->pluck('id_orang'))
                ->get();
            return view('admin.pages.akun.edit', compact('akun', 'roles', 'orangs'));
        } catch (Exception $e) {
            return redirect()->route('admin.akun.index')->with('error', 'Gagal memuat form edit akun: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Akun $id)
    {
        try {
            $akun = $id;
            $request->validate([
                'email' => 'required|email|unique:akuns,email,' . $akun->id_akun . ',id_akun',
                'password' => 'nullable|min:6',
                'id_role' => 'required|exists:roles,id_role',
                'id_orang' => 'nullable|exists:orangs,id_orang',
                'status_aktif' => 'required|boolean',
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Email tidak valid.',
                'email.unique' => 'Email sudah digunakan.',
                'password.min' => 'Password baru minimal 6 karakter.',
                'id_role.required' => 'Pilih role untuk akun.',
                'id_role.exists' => 'Role yang dipilih tidak valid.',
                'id_orang.exists' => 'Orang yang dipilih tidak valid.',
                'status_aktif.required' => 'Status aktif wajib dipilih.',
            ]);

            $akun->update([
                'id_role' => $request->id_role,
                'id_orang' => $request->id_orang,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $akun->password,
                'status_aktif' => $request->status_aktif,
            ]);

            return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui akun: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Akun $id)
    {
        try {
            $id->delete();
            return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()->route('admin.akun.index')->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}