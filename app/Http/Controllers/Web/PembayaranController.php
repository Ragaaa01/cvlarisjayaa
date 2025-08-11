<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Orang;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Pembayaran::with(['orang'])->whereRaw('jumlah_pembayaran >= total_transaksi');
                
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('nama_orang', function ($pembayaran) {
                        return $pembayaran->orang ? $pembayaran->orang->nama_lengkap : '-';
                    })
                    ->addColumn('total_transaksi', function ($pembayaran) {
                        return 'Rp ' . number_format($pembayaran->total_transaksi, 2, ',', '.');
                    })
                    ->addColumn('jumlah_pembayaran', function ($pembayaran) {
                        return 'Rp ' . number_format($pembayaran->jumlah_pembayaran, 2, ',', '.');
                    })
                    ->addColumn('sisa_pembayaran', function ($pembayaran) {
                        $sisa = $pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran;
                        return 'Rp ' . number_format($sisa, 2, ',', '.');
                    })
                    ->addColumn('tanggal_pembayaran', function ($pembayaran) {
                        return $pembayaran->tanggal_pembayaran instanceof \Carbon\Carbon 
                            ? $pembayaran->tanggal_pembayaran->format('Y-m-d')
                            : ($pembayaran->tanggal_pembayaran ? substr($pembayaran->tanggal_pembayaran, 0, 10) : '-');
                    })
                    ->addColumn('action', function ($pembayaran) {
                        return '
                            <div class="action-buttons">
                                <a href="' . route('pembayaran.show', $pembayaran->id_pembayaran) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>
                                <a href="' . route('pembayaran.print', $pembayaran->id_pembayaran) . '" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf action-icon"></i>
                                </a>
                            </div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            $orangs = Orang::all();
            return view('admin.pages.pembayaran.index', compact('orangs'));
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar pembayaran. Silakan coba lagi.');
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Request data untuk pembayaran.update:', $request->all());

        $validator = Validator::make($request->all(), [
            'id_orang' => 'required|exists:orangs,id_orang',
            'id_transaksi' => 'required|exists:transaksis,id_transaksi',
            'total_transaksi' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('Validasi gagal di pembayaran.update:', $validator->errors()->toArray());
            return redirect()->route('transaksi.show', $request->id_transaksi)
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::findOrFail($request->id_transaksi);
            
            // Cari pembayaran yang ada untuk transaksi ini
            $pembayaran = Pembayaran::where('id_pembayaran', $id)
                ->where('id_orang', $request->id_orang)
                ->where('total_transaksi', $request->total_transaksi)
                ->where('tanggal_pembayaran', '>=', $transaksi->tanggal_transaksi)
                ->first();

            $newJumlahPembayaran = $request->jumlah_pembayaran;
            if ($pembayaran) {
                $newJumlahPembayaran += $pembayaran->jumlah_pembayaran;
            }

            if ($newJumlahPembayaran > $request->total_transaksi) {
                Log::warning('Jumlah pembayaran melebihi total transaksi untuk id_transaksi: ' . $request->id_transaksi);
                return redirect()->route('transaksi.show', $request->id_transaksi)
                    ->with('error', 'Jumlah pembayaran melebihi total transaksi.')
                    ->withInput();
            }

            if ($pembayaran) {
                // Update pembayaran yang ada
                $pembayaran->update([
                    'jumlah_pembayaran' => $newJumlahPembayaran,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'tanggal_pembayaran' => Carbon::now()->toDateString(),
                    'waktu_pembayaran' => Carbon::now()->toTimeString(),
                ]);
            } else {
                // Buat pembayaran baru
                Pembayaran::create([
                    'id_orang' => $request->id_orang,
                    'total_transaksi' => $request->total_transaksi,
                    'jumlah_pembayaran' => $request->jumlah_pembayaran,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'tanggal_pembayaran' => Carbon::now()->toDateString(),
                    'waktu_pembayaran' => Carbon::now()->toTimeString(),
                ]);
            }

            DB::commit();
            Log::info('Pembayaran berhasil diperbarui untuk id_transaksi: ' . $request->id_transaksi);
            return redirect()->route('transaksi.show', $request->id_transaksi)
                ->with('success', 'Pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui pembayaran: ' . $e->getMessage());
            return redirect()->route('transaksi.show', $request->id_transaksi)
                ->with('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pembayaran = Pembayaran::with(['orang'])->findOrFail($id);
        $riwayat_pembayaran = Pembayaran::where('id_orang', $pembayaran->id_orang)
                                        ->where('total_transaksi', $pembayaran->total_transaksi)
                                        ->where('tanggal_pembayaran', '>=', $pembayaran->tanggal_pembayaran)
                                        ->get();
        return view('admin.pages.pembayaran.show', compact('pembayaran', 'riwayat_pembayaran'));
    }

    public function print($id)
    {
        try {
            $pembayaran = Pembayaran::with(['orang'])->findOrFail($id);
            $pdf = Pdf::loadView('admin.pages.pembayaran.pembayaran_print', compact('pembayaran'));
            return $pdf->download('nota_pembayaran_' . $pembayaran->id_pembayaran . '_' . date('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mencetak nota pembayaran ke PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak nota pembayaran ke PDF: ' . $e->getMessage());
        }
    }
}