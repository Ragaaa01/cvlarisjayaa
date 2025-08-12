@extends('admin.layouts.base')
@section('title', 'Detail Transaksi')

@section('styles')
    <style>
        .table th, .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .table th {
            text-align: center;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        thead.background {
            background-color: #014A7F !important;
            color: white;
        }
        .btn-custom {
            background-color: #014A7F !important;
            color: white !important;
            border: none;
        }
        .btn-custom:hover {
            background-color: #001B36 !important;
            color: white !important;
        }
        .status-label {
            font-size: 0.9rem;
            font-weight: bold;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .status-belum-dibayar {
            background-color: #dc3545;
        }
        .status-belum-lunas {
            background-color: #ffc107;
        }
        .status-lunas {
            background-color: #28a745;
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endsection

@section('content')
<h2>Detail Transaksi</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Transaksi</h6>
        <div>
            <a href="{{ route('transaksi.print', $transaksi->id_transaksi) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Pelanggan:</strong> {{ $transaksi->orang->nama_lengkap }}</p>
                <p><strong>Total Transaksi:</strong> Rp {{ number_format($transaksi->total_transaksi, 2, ',', '.') }}</p>
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d-m-Y') }}</p>
                <p><strong>Waktu:</strong> {{ $transaksi->waktu_transaksi }}</p>
                @php
                    $statusPembayaran = 'Belum Dibayar';
                    $sisaPembayaran = $transaksi->total_transaksi;
                    if ($pembayaran) {
                        if ($pembayaran->jumlah_pembayaran >= $pembayaran->total_transaksi) {
                            $statusPembayaran = 'Lunas';
                            $sisaPembayaran = 0;
                        } elseif ($pembayaran->jumlah_pembayaran > 0 || $pembayaran->metode_pembayaran !== 'Belum Dibayar') {
                            $statusPembayaran = 'Belum Lunas';
                            $sisaPembayaran = $pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran;
                        }
                    }
                @endphp
                <p><strong>Status Pembayaran:</strong> 
                    <span class="status-label status-{{ strtolower(str_replace(' ', '-', $statusPembayaran)) }}">{{ $statusPembayaran }}</span>
                </p>
                <p><strong>Sisa Pembayaran:</strong> Rp {{ number_format($sisaPembayaran, 2, ',', '.') }}</p>
            </div>
        </div>

        <h5 class="mt-4">Detail Transaksi</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="background">
                    <tr>
                        <th>No</th>
                        <th>Jenis Transaksi</th>
                        <th>Kode Tabung</th>
                        <th>Jenis Tabung</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi->transaksiDetails as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->jenisTransaksiDetail->jenis_transaksi }}</td>
                            <td>{{ $detail->tabung ? $detail->tabung->kode_tabung : '-' }}</td>
                            <td>{{ $detail->tabung && $detail->tabung->jenisTabung ? $detail->tabung->jenisTabung->nama_jenis : '-' }}</td>
                            <td>Rp {{ number_format($detail->harga, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($statusPembayaran !== 'Lunas')
            <button type="button" class="btn btn-success mt-3" data-toggle="modal" data-target="#pembayaranModal"
                data-id="{{ $pembayaran ? $pembayaran->id_pembayaran : 0 }}"
                data-id_orang="{{ $transaksi->id_orang }}"
                data-total_transaksi="{{ $transaksi->total_transaksi }}"
                data-sisa="{{ $sisaPembayaran }}"
                data-id_transaksi="{{ $transaksi->id_transaksi }}">
                <i class="fas fa-money-bill mr-1"></i> Lakukan Pembayaran
            </button>
        @endif
    </div>
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
            <form id="pembayaranForm" method="POST" action="">
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
                            <option value="{{ $transaksi->id_orang }}">{{ $transaksi->orang->nama_lengkap }}</option>
                        </select>
                        <input type="hidden" name="id_orang" id="modal_id_orang_hidden" value="">
                        <input type="hidden" name="id_transaksi" id="modal_id_transaksi" value="">
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $('#pembayaranModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var id_orang = button.data('id_orang');
                var total_transaksi = button.data('total_transaksi');
                var sisa = button.data('sisa');
                var id_transaksi = button.data('id_transaksi');

                console.log('Modal opened with data:', {
                    id: id,
                    id_orang: id_orang,
                    total_transaksi: total_transaksi,
                    sisa: sisa,
                    id_transaksi: id_transaksi
                });

                var modal = $(this);
                modal.find('#pembayaranForm').attr('action', '{{ route("pembayaran.update", ":id") }}'.replace(':id', id || 0));
                modal.find('#modal_id_orang').val(id_orang).trigger('change');
                modal.find('#modal_id_orang_hidden').val(id_orang);
                modal.find('#modal_id_transaksi').val(id_transaksi);
                modal.find('#modal_total_transaksi').val(total_transaksi);
                modal.find('#modal_sisa_pembayaran').val(sisa);
                modal.find('#modal_jumlah_pembayaran').val('');
                modal.find('#modal_metode_pembayaran').val('');
            });

            $('#pembayaranForm').on('submit', function(e) {
                var formData = $(this).serialize();
                console.log('Form submitted with data:', formData);
            });
        });
    </script>
@endpush