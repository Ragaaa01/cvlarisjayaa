<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\Orang;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrangController extends Controller
{
    /**
     * Menampilkan daftar orang.
     */
    public function index()
    {
        try {
            Log::info('Mengakses halaman index orang oleh pengguna: ' . (auth()->check() ? auth()->user()->id_akun : 'Guest'));
            $orangs = Orang::with('perusahaan')->oldest()->paginate(10);
            Log::info('Data orang diambil: ' . count($orangs) . ' record');
            return view('admin.pages.orang.index', compact('orangs'));
        } catch (Exception $e) {
            Log::error('Gagal memuat daftar orang: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->with('error', 'Gagal memuat daftar orang: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail data orang.
     */
    public function show($id)
    {
        try {
            Log::info('Memulai memuat detail orang ID: ' . $id);
            $orang = Orang::with('perusahaan')->findOrFail($id);
            Log::info('Data orang ditemukan: ' . $orang->nama_lengkap);
            return view('admin.pages.orang.show', compact('orang'));
        } catch (Exception $e) {
            Log::error('Gagal memuat data orang ID: ' . $id . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->with('error', 'Gagal memuat data orang: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk membuat data orang baru.
     */
    public function create()
    {
        try {
            // Ambil perusahaan yang belum terkait dengan orang lain
            $perusahaans = Perusahaan::whereDoesntHave('orangs')->get();
            return view('admin.pages.orang.create', compact('perusahaans'));
        } catch (Exception $e) {
            Log::error('Gagal memuat form create orang: ' . $e->getMessage());
            return redirect()->route('admin.orang.index')->with('error', 'Gagal memuat form: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan data orang baru ke database.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Memulai menyimpan data orang baru oleh pengguna: ' . (auth()->check() ? auth()->user()->id_akun : 'Guest'));
            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'required|string|max:16|unique:orangs,nik',
                'no_telepon' => 'required|string|max:15',
                'alamat' => 'required|string',
                'id_perusahaan' => 'nullable|exists:perusahaans,id_perusahaan',
            ]);

            if ($validator->fails()) {
                Log::warning('Validasi gagal saat menyimpan data orang: ' . json_encode($validator->errors()));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $orang = Orang::create($request->only(['nama_lengkap', 'nik', 'no_telepon', 'alamat']));
            Log::info('Data orang berhasil disimpan: ' . $orang->nama_lengkap);

            // Simpan relasi dengan perusahaan jika ada
            if ($request->filled('id_perusahaan')) {
                $orang->perusahaan()->attach($request->id_perusahaan, ['status' => 'Karyawan']);
                Log::info('Relasi orang-perusahaan disimpan: Orang ID ' . $orang->id_orang . ', Perusahaan ID ' . $request->id_perusahaan);
            }

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menambahkan data orang: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->back()->with('error', 'Gagal menambahkan data orang: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit data orang.
     */
    public function edit($id)
    {
        try {
            Log::info('Memulai memuat form edit orang ID: ' . $id);
            $orang = Orang::findOrFail($id);
            // Ambil perusahaan yang belum terkait, kecuali perusahaan yang sudah terkait dengan orang ini
            $perusahaans = Perusahaan::whereDoesntHave('orangs', function ($query) use ($id) {
                $query->where('id_orang', '!=', $id);
            })->get();
            Log::info('Data orang ditemukan untuk edit: ' . $orang->nama_lengkap);
            return view('admin.pages.orang.edit', compact('orang', 'perusahaans'));
        } catch (Exception $e) {
            Log::error('Gagal memuat data orang untuk edit ID: ' . $id . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->with('error', 'Gagal memuat data orang: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui data orang di database.
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Memulai memperbarui data orang ID: ' . $id);
            $orang = Orang::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'nik' => 'required|string|max:16|unique:orangs,nik,' . $id . ',id_orang',
                'no_telepon' => 'required|string|max:15',
                'alamat' => 'required|string',
                'id_perusahaan' => 'nullable|exists:perusahaans,id_perusahaan',
            ]);

            if ($validator->fails()) {
                Log::warning('Validasi gagal saat memperbarui data orang: ' . json_encode($validator->errors()));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $isChanged =
                $orang->nama_lengkap !== $request->nama_lengkap ||
                $orang->nik !== $request->nik ||
                $orang->no_telepon !== $request->no_telepon ||
                $orang->alamat !== $request->alamat ||
                ($request->filled('id_perusahaan') && !$orang->perusahaan->contains('id_perusahaan', $request->id_perusahaan)) ||
                (!$request->filled('id_perusahaan') && $orang->perusahaan->isNotEmpty());

            // Update data orang
            $orang->update($request->only(['nama_lengkap', 'nik', 'no_telepon', 'alamat']));
            Log::info('Data orang berhasil diperbarui: ' . $orang->nama_lengkap);

            // Update relasi perusahaan
            if ($request->filled('id_perusahaan')) {
                $currentPerusahaan = $orang->perusahaan()->first();
                if (!$currentPerusahaan || $currentPerusahaan->id_perusahaan != $request->id_perusahaan) {
                    $orang->perusahaan()->sync([$request->id_perusahaan => ['status' => 'Karyawan']]);
                    Log::info('Relasi orang-perusahaan diperbarui: Orang ID ' . $orang->id_orang . ', Perusahaan ID ' . $request->id_perusahaan);
                }
            } else {
                $orang->perusahaan()->detach();
                Log::info('Relasi orang-perusahaan dihapus: Orang ID ' . $orang->id_orang);
            }

            if (!$isChanged) {
                Log::info('Tidak ada perubahan pada data orang ID: ' . $id);
                return redirect()->route('admin.orang.index')->with('info', 'Tidak ada perubahan pada data.');
            }

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui data orang ID: ' . $id . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->back()->with('error', 'Gagal memperbarui data orang: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus data orang dari database.
     */
    public function destroy($id)
    {
        try {
            Log::info('Memulai menghapus data orang ID: ' . $id);
            $orang = Orang::findOrFail($id);
            $orang->delete();
            Log::info('Data orang berhasil dihapus: ' . $orang->nama_lengkap);

            return redirect()->route('admin.orang.index')->with('success', 'Data orang berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Gagal menghapus data orang ID: ' . $id . ' - ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->with('error', 'Gagal menghapus data orang: ' . $e->getMessage());
        }
    }

    /**
     * Ekspor data orang ke Excel.
     */
    public function exportExcel()
    {
        try {
            Log::info('Fungsi exportExcel dipanggil oleh pengguna: ' . (auth()->check() ? auth()->user()->id_akun : 'Guest'));
            $orangs = Orang::with('perusahaan')->get()->map(function ($orang, $index) {
                return [
                    'Nomor' => $index + 1,
                    'Nama Lengkap' => $orang->nama_lengkap,
                    'NIK' => $orang->nik,
                    'No Telepon' => $orang->no_telepon,
                    'Alamat' => $orang->alamat,
                    'Perusahaan' => $orang->perusahaan->isNotEmpty() ? $orang->perusahaan->pluck('nama_perusahaan')->implode(', ') : '-',
                ];
            })->toArray();

            Log::info('Data orang untuk Excel:', ['count' => count($orangs)]);

            return \Maatwebsite\Excel\Facades\Excel::download(new class($orangs) implements \Maatwebsite\Excel\Concerns\FromArray {
                protected $data;

                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return array_merge([array_keys($this->data[0])], $this->data);
                }
            }, 'Data_Orang_' . date('Y-m-d_His') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Gagal mengekspor data orang ke Excel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->withErrors(['error' => 'Gagal mengekspor data ke Excel: ' . $e->getMessage()]);
        }
    }

    /**
     * Ekspor data orang ke PDF.
     */
    public function exportPdf()
    {
        try {
            Log::info('Fungsi exportPdf dipanggil oleh pengguna: ' . (auth()->check() ? auth()->user()->id_akun : 'Guest'));
            $orangs = Orang::with('perusahaan')->get();
            Log::info('Data orang untuk PDF:', ['count' => count($orangs)]);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pages.orang.orang_export', compact('orangs'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Data_Orang_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mengekspor data orang ke PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => auth()->check() ? auth()->user()->id_akun : 'Guest'
            ]);
            return redirect()->route('admin.orang.index')->withErrors(['error' => 'Gagal mengekspor data ke PDF: ' . $e->getMessage()]);
        }
    }
}