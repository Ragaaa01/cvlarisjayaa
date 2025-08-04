<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Menampilkan daftar role dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Role::query();

                // Terapkan pencarian global jika ada
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = $request->input('search.value');
                    $query->where('nama_role', 'like', '%' . $search . '%');
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('action', function ($role) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.role.show', $role->id_role) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.role.edit', $role->id_role) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.role.destroy', $role->id_role) . '" method="POST" class="d-inline">
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

            return view('admin.pages.role.index');
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar role. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat role baru.
     */
    public function create()
    {
        try {
            return view('admin.pages.role.create');
        } catch (Exception $e) {
            Log::error('Gagal memuat form tambah role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah role. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan role baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_role' => 'required|string|max:255|unique:roles,nama_role',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Role::create([
                'nama_role' => $request->nama_role,
            ]);

            return redirect()->route('admin.role.index')->with('success', 'Data role berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan data role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data role. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail role.
     */
    public function show($id_role)
    {
        try {
            $role = Role::findOrFail($id_role);
            return view('admin.pages.role.show', compact('role'));
        } catch (Exception $e) {
            Log::error('Gagal memuat detail role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail role. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit role.
     */
    public function edit($id_role)
    {
        try {
            $role = Role::findOrFail($id_role);
            return view('admin.pages.role.edit', compact('role'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form edit role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit role. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data role di database.
     */
    public function update(Request $request, $id_role)
    {
        try {
            $role = Role::findOrFail($id_role);

            $validator = Validator::make($request->all(), [
                'nama_role' => 'required|string|max:255|unique:roles,nama_role,' . $id_role . ',id_role',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $role->update([
                'nama_role' => $request->nama_role,
            ]);

            return redirect()->route('admin.role.index')->with('success', 'Data role berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data role. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data role dari database.
     */
    public function destroy($id_role)
    {
        try {
            $role = Role::findOrFail($id_role);
            $role->delete();

            return redirect()->route('admin.role.index')->with('success', 'Data role berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data role. Silakan coba lagi.');
        }
    }

    /**
     * Export data role ke Excel.
     */
    public function exportExcel()
    {
        try {
            $roles = Role::all();
            return Excel::download(new class($roles) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $roles;

                public function __construct($roles)
                {
                    $this->roles = $roles;
                }

                public function collection()
                {
                    return $this->roles->map(function ($role, $index) {
                        return [
                            'No' => $index + 1,
                            'Nama Role' => $role->nama_role,
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Nama Role',
                    ];
                }
            }, 'data_role_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export data role ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data role ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data role ke PDF.
     */
    public function exportPdf()
    {
        try {
            $roles = Role::all();
            $pdf = Pdf::loadView('admin.pages.role.role_export', compact('roles'));
            return $pdf->download('data_role_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            Log::error('Gagal export data role ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data role ke PDF. Silakan coba lagi.');
        }
    }
}