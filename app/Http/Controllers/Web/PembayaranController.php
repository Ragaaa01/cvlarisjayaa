<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Orang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar pembayaran dengan server-side DataTables.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $status = $request->query('status', 'belum_lunas'); // Default ke belum lunas
                $query = Pembayaran::with(['orang']);
                
                if ($status === 'lunas') {
                    $query->whereRaw('jumlah_pembayaran >= total_transaksi');
                } else {
                    $query->whereRaw('jumlah_pembayaran < total_transaksi')
                          ->orWhere('metode_pembayaran', 'Belum Dibayar');
                }

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
                        $actionButtons = '
                            <div class="action-buttons">
                                <a href="' . route('pembayaran.show', $pembayaran->id_pembayaran) . '" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye action-icon"></i>
                                </a>';
                        if ($pembayaran->jumlah_pembayaran < $pembayaran->total_transaksi || $pembayaran->metode_pembayaran === 'Belum Dibayar') {
                            $actionButtons .= '
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#pembayaranModal" 
                                    data-id="' . $pembayaran->id_pembayaran . '" 
                                    data-id_orang="' . $pembayaran->id_orang . '" 
                                    data-total_transaksi="' . $pembayaran->total_transaksi . '" 
                                    data-sisa="' . ($pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran) . '">
                                    <i class="fas fa-money-bill action-icon"></i>
                                </button>';
                        }
                        $actionButtons .= '</div>';
                        return $actionButtons;
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

    /**
     * Memperbarui pembayaran di database.
     */
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_orang' => 'required|exists:orangs,id_orang',
            'total_transaksi' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Akumulasi jumlah_pembayaran
            $newJumlahPembayaran = $pembayaran->jumlah_pembayaran + $request->jumlah_pembayaran;
            if ($newJumlahPembayaran > $pembayaran->total_transaksi) {
                return redirect()->back()->with('error', 'Jumlah pembayaran melebihi total transaksi.')->withInput();
            }

            $pembayaran->update([
                'id_orang' => $request->id_orang,
                'total_transaksi' => $request->total_transaksi,
                'jumlah_pembayaran' => $newJumlahPembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal_pembayaran' => Carbon::now()->toDateString(),
                'waktu_pembayaran' => Carbon::now()->toTimeString(),
            ]);

            DB::commit();
            return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui pembayaran: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail dan riwayat pembayaran untuk pelanggan.
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with(['orang'])->findOrFail($id);
        $riwayat_pembayaran = Pembayaran::where('id_orang', $pembayaran->id_orang)->get();
        return view('admin.pages.pembayaran.show', compact('pembayaran', 'riwayat_pembayaran'));
    }
}