@extends('admin.layouts.base')
@section('title', 'Detail Tabung')

@section('styles')
<style>
    .detail-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.2rem 0.4rem rgba(0,0,0,0.1);
        transition: transform 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-3px);
    }
    .card-header {
        background-color: #014A7F;
        color: white;
        border-radius: 0.75rem 0.75rem 0 0;
        padding: 1rem 1.5rem;
    }
    .nav-tabs .nav-link {
        color: #333;
        font-weight: 500;
        border: none;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link.active {
        color: #014A7F;
        border-bottom: 2px solid #014A7F;
        background-color: transparent;
    }
    .nav-tabs .nav-link:hover {
        color: #014A7F;
        border-bottom: 2px solid #b8daff;
    }
    .tab-content {
        padding: 1.5rem;
    }
    .detail-item {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #333;
        width: 200px;
        flex-shrink: 0;
    }
    .detail-value {
        color: #555;
        flex-grow: 1;
    }
    .status-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .status-dipinjam {
        background-color: #ffe5e5;
        color: #dc3545;
    }
    .status-tersedia {
        background-color: #e6ffed;
        color: #28a745;
    }
    .btn-custom {
        background-color: #014A7F !important;
        color: white !important;
        border: none;
        border-radius: 0.5rem;
        padding: 0.5rem 1.5rem;
        transition: background-color 0.3s ease;
    }
    .btn-custom:hover {
        background-color: #001B36 !important;
    }
    .table-responsive {
        margin-top: 1.5rem;
    }
    .table th, .table td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 0.75rem;
    }
    .table th {
        background-color: #014A7F;
        color: white;
        text-align: center;
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .no-data {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 1.5rem;
    }
    .icon-status {
        font-size: 1.1rem;
    }
</style>
@endsection

@section('content')
<h2 class="mb-4">Detail Tabung</h2>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Informasi Tabung</h6>
    </div>
    <div class="card-body detail-container">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="tabungTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Informasi Dasar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">Riwayat Tabung</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="tabungTabsContent">
            <!-- Informasi Dasar -->
            <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                <div class="detail-item">
                    <div class="detail-label">Kode Tabung</div>
                    <div class="detail-value">{{ $tabung->kode_tabung }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Jenis Tabung</div>
                    <div class="detail-value">{{ $tabung->jenisTabung->nama_jenis }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status Tabung</div>
                    <div class="detail-value">{{ $tabung->statusTabung->status_tabung }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status Peminjaman</div>
                    <div class="detail-value">
                        @if ($peminjamanSaatIni)
                            <span class="status-badge status-dipinjam">
                                <i class="fas fa-exclamation-circle icon-status"></i> Dipinjam
                            </span>
                            <div class="mt-2">
                                <div><strong>Peminjam:</strong> 
                                    @if ($peminjamanSaatIni->transaksiDetail->transaksi->orang->orangMitras->isNotEmpty())
                                        {{ $peminjamanSaatIni->transaksiDetail->transaksi->orang->orangMitras->first()->mitra->nama_mitra }}
                                    @else
                                        {{ $peminjamanSaatIni->transaksiDetail->transaksi->orang->nama_lengkap }}
                                    @endif
                                </div>
                                <div><strong>Tanggal Pinjam:</strong> {{ $peminjamanSaatIni->tanggal_pinjam->format('d-m-Y') }}</div>
                                <div><strong>Waktu Pinjam:</strong> {{ $peminjamanSaatIni->waktu_pinjam }}</div>
                            </div>
                        @else
                            <span class="status-badge status-tersedia">
                                <i class="fas fa-check-circle icon-status"></i> Tersedia
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Riwayat Tabung -->
            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Waktu Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status Tabung</th>
                                <th>Kondisi Tabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayatPeminjaman as $index => $riwayat)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if ($riwayat->transaksiDetail->transaksi->orang->orangMitras->isNotEmpty())
                                            {{ $riwayat->transaksiDetail->transaksi->orang->orangMitras->first()->mitra->nama_mitra }}
                                        @else
                                            {{ $riwayat->transaksiDetail->transaksi->orang->nama_lengkap }}
                                        @endif
                                    </td>
                                    <td>{{ $riwayat->tanggal_pinjam->format('d-m-Y') }}</td>
                                    <td>{{ $riwayat->waktu_pinjam }}</td>
                                    <td>{{ $riwayat->tanggal_pengembalian ? $riwayat->tanggal_pengembalian->format('d-m-Y') : '-' }}</td>
                                    <td>
                                        @if ($riwayat->tanggal_pengembalian)
                                            Dikembalikan
                                        @else
                                            {{ $riwayat->statusTabung->status_tabung }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($riwayat->tanggal_pengembalian)
                                            @if ($riwayat->denda_kondisi_tabung == 0)
                                                Baik
                                            @elseif ($riwayat->denda_kondisi_tabung == $riwayat->deposit)
                                                Hilang
                                            @else
                                                Rusak
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="no-data">Belum ada riwayat tabung untuk tabung ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-right mt-3">
    <a href="{{ route('admin.tabung.index') }}" class="btn btn-custom">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@endsection

@section('scripts')
<script>
    // Aktifkan tab berdasarkan hash URL
    $(document).ready(function() {
        var hash = window.location.hash;
        if (hash) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
        }

        // Simpan tab aktif ke URL
        $('.nav-tabs a').on('shown.bs.tab', function(e) {
            window.location.hash = e.target.hash;
        });
    });
</script>
@endsection