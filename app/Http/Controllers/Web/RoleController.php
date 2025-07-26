<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::all();
            return view('admin.pages.role.index', compact('roles'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar role. Silakan coba lagi.');
        }
    }

    public function create()
    {
        try {
            return view('admin.pages.role.create');
        } catch (\Exception $e) {
            Log::error('Gagal memuat form tambah role: ' . $e->getMessage());
            return redirect()->route('admin.role.index')->with('error', 'Gagal memuat form tambah role. Silakan coba lagi.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|string|max:255|unique:roles,nama_role',
        ]);

        try {
            DB::beginTransaction();
            Role::create([
                'nama_role' => $request->nama_role,
            ]);
            DB::commit();

            return redirect()->route('admin.role.index')
                ->with('success', 'Role berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan role. Silakan coba lagi.');
        }
    }

    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);
            return view('admin.pages.role.show', compact('role'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail role: ' . $e->getMessage());
            return redirect()->route('admin.role.index')->with('error', 'Gagal memuat detail role. Silakan coba lagi.');
        }
    }

    public function edit($id)
    {
        try {
            $role = Role::findOrFail($id);
            return view('admin.pages.role.edit', compact('role'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat form edit role: ' . $e->getMessage());
            return redirect()->route('admin.role.index')->with('error', 'Gagal memuat form edit role. Silakan coba lagi.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_role' => 'required|string|max:255|unique:roles,nama_role,' . $id . ',id_role',
        ]);

        try {
            DB::beginTransaction();
            $role = Role::findOrFail($id);
            $role->update([
                'nama_role' => $request->nama_role,
            ]);
            DB::commit();

            return redirect()->route('admin.role.index')
                ->with('success', 'Role berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui role. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $role = Role::findOrFail($id);
            $role->delete();
            DB::commit();

            return redirect()->route('admin.role.index')
                ->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus role. Silakan coba lagi.');
        }
    }
}