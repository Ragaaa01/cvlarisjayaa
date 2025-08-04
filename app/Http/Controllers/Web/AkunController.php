<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Akun;
use App\Models\Role;
use App\Models\Orang;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AkunController extends Controller
{
    /**
     * Menampilkan daftar akun dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Akun::with(['role', 'orang']);

                // Terapkan filter berdasarkan role jika ada dan tidak kosong
                if ($request->has('id_role') && !empty($request->id_role)) {
                    $query->where('id_role', $request->id_role);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_orang', function ($akun) {
                        return $akun->orang->nama_lengkap;
                    })
                    ->addColumn('role', function ($akun) {
                        return $akun->role->nama_role;
                    })
                    ->addColumn('status_aktif', function ($akun) {
                        return $akun->status_aktif ? 'Aktif' : 'Non-Aktif';
                    })
                    ->addColumn('action', function ($akun) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('admin.akun.show', $akun->id_akuns) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('admin.akun.edit', $akun->id_akuns) . '" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit action-icon"></i>
                                </a>
                                <form action="' . route('admin.akun.destroy', $akun->id_akuns) . '" method="POST" class="d-inline">
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

            $roles = Role::all(); // Untuk dropdown filter
            return view('admin.pages.akun.index', compact('roles'));
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar akun. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk membuat akun baru.
     */
    public function create()
    {
        try {
            // Ambil orang yang belum memiliki akun
            $usedOrangIds = Akun::pluck('id_orang');
            $orangs = Orang::whereNotIn('id_orang', $usedOrangIds)->get();
            $roles = Role::all();
            return view('admin.pages.akun.create', compact('orangs', 'roles'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form tambah akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form tambah akun. Silakan coba lagi.');
        }
    }

    /**
     * Menyimpan akun baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|max:255|unique:akuns,email',
                'password' => 'nullable|string|min:8|confirmed',
                'id_role' => 'required|exists:roles,id_role',
                'id_orang' => 'required|exists:orangs,id_orang',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Cek apakah orang sudah memiliki akun
            $exists = Akun::where('id_orang', $request->id_orang)->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Orang ini sudah memiliki akun.')->withInput();
            }

            Akun::create([
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : null,
                'id_role' => $request->id_role,
                'id_orang' => $request->id_orang,
                'status_aktif' => true, // Default aktif
            ]);

            return redirect()->route('admin.akun.index')->with('success', 'Data akun berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menyimpan data akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data akun. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menampilkan detail akun.
     */
    public function show($id_akuns)
    {
        try {
            $akun = Akun::with(['role', 'orang'])->findOrFail($id_akuns);
            return view('admin.pages.akun.show', compact('akun'));
        } catch (Exception $e) {
            Log::error('Gagal memuat detail akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail akun. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit akun.
     */
    public function edit($id_akuns)
    {
        try {
            $akun = Akun::findOrFail($id_akuns);
            $roles = Role::all();
            // Ambil semua orang kecuali yang sudah digunakan oleh akun lain
            $usedOrangIds = Akun::where('id_akuns', '!=', $id_akuns)->pluck('id_orang');
            $orangs = Orang::whereNotIn('id_orang', $usedOrangIds)->orWhere('id_orang', $akun->id_orang)->get();
            return view('admin.pages.akun.edit', compact('akun', 'roles', 'orangs'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form edit akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat form edit akun. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui data akun di database.
     */
    public function update(Request $request, $id_akuns)
    {
        try {
            $akun = Akun::findOrFail($id_akuns);

            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|max:255|unique:akuns,email,' . $id_akuns . ',id_akuns',
                'password' => 'nullable|string|min:8|confirmed',
                'id_role' => 'required|exists:roles,id_role',
                'id_orang' => 'required|exists:orangs,id_orang',
                'status_aktif' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Cek apakah orang sudah digunakan oleh akun lain
            $exists = Akun::where('id_orang', $request->id_orang)
                          ->where('id_akuns', '!=', $id_akuns)
                          ->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'Orang ini sudah memiliki akun lain.')->withInput();
            }

            $data = [
                'email' => $request->email,
                'id_role' => $request->id_role,
                'id_orang' => $request->id_orang,
                'status_aktif' => $request->status_aktif,
            ];

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $akun->update($data);

            return redirect()->route('admin.akun.index')->with('success', 'Data akun berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data akun. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Menghapus data akun dari database.
     */
    public function destroy($id_akuns)
    {
        try {
            $akun = Akun::findOrFail($id_akuns);
            $akun->delete();

            return redirect()->route('admin.akun.index')->with('success', 'Data akun berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data akun: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data akun. Silakan coba lagi.');
        }
    }

    /**
     * Export data akun ke Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = Akun::with(['role', 'orang']);

            // Terapkan filter berdasarkan role jika ada dan tidak kosong
            if ($request->has('id_role') && !empty($request->id_role)) {
                $query->where('id_role', $request->id_role);
            }

            $akuns = $query->get();
            return Excel::download(new class($akuns) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $akuns;

                public function __construct($akuns)
                {
                    $this->akuns = $akuns;
                }

                public function collection()
                {
                    return $this->akuns->map(function ($akun, $index) {
                        return [
                            'No' => $index + 1,
                            'Email' => $akun->email ?? '-',
                            'Nama Orang' => $akun->orang->nama_lengkap,
                            'Role' => $akun->role->nama_role,
                            'Status Aktif' => $akun->status_aktif ? 'Aktif' : 'Non-Aktif',
                        ];
                    });
                }

                public function headings(): array
                {
                    return [
                        'No',
                        'Email',
                        'Nama Orang',
                        'Role',
                        'Status Aktif',
                    ];
                }
            }, 'data_akun_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export data akun ke Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data akun ke Excel. Silakan coba lagi.');
        }
    }

    /**
     * Export data akun ke PDF.
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Akun::with(['role', 'orang']);

            // Terapkan filter berdasarkan role jika ada dan tidak kosong
            if ($request->has('id_role') && !empty($request->id_role)) {
                $query->where('id_role', $request->id_role);
            }

            $akuns = $query->get();
            $pdf = Pdf::loadView('admin.pages.akun.akun_export', compact('akuns'));
            return $pdf->download('data_akun_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            Log::error('Gagal export data akun ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export data akun ke PDF. Silakan coba lagi.');
        }
    }
}