@extends('admin.layouts.base')
@section('title', 'Detail Transaksi')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .table .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .modal-content {
            border-radius: 8px;
        }
        .modal-header {
            background-color: #014A7F;
            color: white;
        }
        .modal-footer {
            border-top: none;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-weight: bold;
        }
        .select2-container {
            width: 100% !important;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
        }
    </style>
@endsection

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Detail Transaksi #{{ $transaksi->id_transaksi }}</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <h5>Informasi Transaksi</h5>
            <table class="table table-bordered">
                <tr>
                    <th>Pelanggan</th>
                    <td>{{ $transaksi->orang->nama_lengkap }}</td>
                </tr>
                <tr>
                    <th>Total Transaksi</th>
                    <td>Rp {{ number_format($transaksi->total_transaksi, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ $transaksi->tanggal_transaksi->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th>Waktu</th>
                    <td>{{ $transaksi->waktu_transaksi }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $transaksi->status_valid ? 'Valid' : 'Batal' }}</td>
                </tr>
            </table>

            <h5 class="mt-4">Detail Transaksi</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Jenis Transaksi</th>
                        <th>Tabung</th>
                        <th>Harga Pinjam</th>
                        <th>Harga Isi Ulang</th>
                        <th>Nilai Deposit</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalHarga = 0;
                    @endphp
                    @foreach ($transaksi->transaksiDetails as $detail)
                        <tr>
                            <td>{{ $detail->jenisTransaksiDetail->jenis_transaksi }}</td>
                            <td>{{ $detail->tabung ? $detail->tabung->kode_tabung . ' (' . $detail->tabung->jenisTabung->nama_jenis . ')' : '-' }}</td>
                            @if (strtolower($detail->jenisTransaksiDetail->jenis_transaksi) === 'peminjaman' && $detail->tabung && $detail->tabung->jenisTabung)
                                <td>Rp {{ number_format($detail->tabung->jenisTabung->harga_pinjam, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->tabung->jenisTabung->harga_isi_ulang, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->tabung->jenisTabung->nilai_deposit, 2, ',', '.') }}</td>
                            @elseif (in_array(strtolower($detail->jenisTransaksiDetail->jenis_transaksi), ['isi ulang', 'isi_ulang']))
                                <td>-</td>
                                <td>Rp {{ number_format($detail->harga, 2, ',', '.') }}</td>
                                <td>-</td>
                            @else
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            @endif
                            <td>Rp {{ number_format($detail->harga, 2, ',', '.') }}</td>
                        </tr>
                        @php
                            $totalHarga += $detail->harga;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="text-right">Total</td>
                        <td>Rp {{ number_format($totalHarga, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-3">
                @php
                    $pembayaran = \App\Models\Pembayaran::where('id_orang', $transaksi->id_orang)
                        ->where('total_transaksi', $transaksi->total_transaksi)
                        ->where('tanggal_pembayaran', $transaksi->tanggal_transaksi)
                        ->first();
                @endphp
                @if ($pembayaran && ($pembayaran->jumlah_pembayaran < $pembayaran->total_transaksi || $pembayaran->metode_pembayaran === 'Belum Dibayar'))
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#pembayaranModal"
                            data-id="{{ $pembayaran->id_pembayaran }}"
                            data-id_orang="{{ $pembayaran->id_orang }}"
                            data-total_transaksi="{{ $pembayaran->total_transaksi }}"
                            data-sisa="{{ $pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran }}">
                        <i class="fas fa-money-bill mr-1"></i> Bayar Sekarang
                    </button>
                @endif
                <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Kembali</a>
            </div>

            <!-- Modal untuk pembayaran -->
            <div class="modal fade" id="pembayaranModal" tabindex="-1" role="dialog" aria-labelledby="pembayaranModalLabel" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pembayaranModalLabel">Lakukan Pembayaran</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="pembayaranForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="modal_id_orang">Pelanggan</label>
                                    <select name="id_orang" id="modal_id_orang" class="form-control select2" disabled required>
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach (\App\Models\Orang::all() as $orang)
                                            <option value="{{ $orang->id_orang }}">{{ $orang->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id_orang" id="modal_id_orang_hidden">
                                </div>
                                <div class="form-group">
                                    <label for="modal_total_transaksi">Total Transaksi</label>
                                    <input type="number" name="total_transaksi" id="modal_total_transaksi" class="form-control" step="0.01" readonly required>
                                </div>
                                <div class="form-group">
                                    <label for="modal_sisa_pembayaran">Sisa Pembayaran</label>
                                    <input type="number" id="modal_sisa_pembayaran" class="form-control" step="0.01" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="modal_jumlah_pembayaran">Jumlah Pembayaran</label>
                                    <input type="number" name="jumlah_pembayaran" id="modal_jumlah_pembayaran" class="form-control" step="0.01" required>
                                    @error('jumlah_pembayaran')
                                        <span class="text-danger">{{ $message }}</span>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="modal_metode_pembayaran">Metode Pembayaran</label>
                                    <select name="metode_pembayaran" id="modal_metode_pembayaran" class="form-control" required>
                                        <option value="">Pilih Metode Pembayaran</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer Bank">Transfer Bank</option>
                                        <option value="Kartu Kredit">Kartu Kredit</option>
                                        <option value="E-Wallet">E-Wallet</option>
                                    </select>
                                    @error('metode_pembayaran')
                                        <span class="text-danger">{{ $message }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Event untuk mengisi form modal
            $('#pembayaranModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var id_orang = button.data('id_orang');
                var total_transaksi = button.data('total_transaksi');
                var sisa = button.data('sisa');

                var modal = $(this);
                modal.find('#pembayaranForm').attr('action', '{{ url("pembayaran") }}/' + id);
                modal.find('#modal_id_orang').val(id_orang).trigger('change');
                modal.find('#modal_id_orang_hidden').val(id_orang);
                modal.find('#modal_total_transaksi').val(total_transaksi);
                modal.find('#modal_sisa_pembayaran').val(sisa);
                modal.find('#modal_jumlah_pembayaran').val('');
                modal.find('#modal_metode_pembayaran').val('');
            });
        });
    </script>
@endpush