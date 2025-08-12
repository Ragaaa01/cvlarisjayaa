@extends('admin.layouts.base')
@section('title', 'Dashboard')

@section('styles')
    <style>
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
        }
        .card-body {
            padding: 1.5rem;
        }
        .text-xs {
            font-size: 0.85rem;
        }
        .h5 {
            font-size: 1.5rem;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #014A7F;
        }
        .btn-primary {
            background-color: #014A7F;
            border-color: #014A7F;
        }
        .btn-primary:hover {
            background-color: #001B36;
            border-color: #001B36;
        }
        .btn-access {
            margin-top: 1rem;
            width: 100%;
            text-align: center;
        }
        .alert-dismissible {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .alert-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        .text-muted {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .h5 {
                font-size: 1.2rem;
            }
            .text-xs {
                font-size: 0.75rem;
            }
            .btn-access {
                font-size: 0.9rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Menampilkan pesan error jika ada -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Content Row: Tabung -->
    <div class="mb-4">
        <h4 class="section-title">Tabung</h4>
        <div class="row">
            <!-- Total Data Tabung -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Data Tabung
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTubes ?? 0, 0, ',', '.') }}</div>
                                @if (($totalTubes ?? 0) == 0)
                                    <div class="text-muted">Tidak ada data tabung.</div>
                                @else
                                    <a href="{{ route('admin.tabung.index') }}" class="btn btn-primary btn-sm btn-access">
                                        <i class="fas fa-list mr-1"></i> Lihat Tabung
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cube fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabung Dipinjam -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Tabung Dipinjam
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($borrowedTubes ?? 0, 0, ',', '.') }}</div>
                                @if (($borrowedTubes ?? 0) == 0)
                                    <div class="text-muted">Tidak ada tabung yang dipinjam.</div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck-moving fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabung Tersedia -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Tabung Tersedia
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($availableTubes ?? 0, 0, ',', '.') }}</div>
                                @if (($availableTubes ?? 0) == 0)
                                    <div class="text-muted">Tidak ada tabung yang tersedia.</div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row: Pelanggan -->
    <div class="mb-4">
        <h4 class="section-title">Pelanggan</h4>
        <div class="row">
            <!-- Pelanggan Perorangan -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Pelanggan Perorangan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($individualCustomers ?? 0, 0, ',', '.') }}</div>
                                @if (($individualCustomers ?? 0) == 0)
                                    <div class="text-muted">Tidak ada pelanggan perorangan.</div>
                                @else
                                    <a href="{{ route('admin.orang.index') }}" class="btn btn-primary btn-sm btn-access">
                                        <i class="fas fa-user mr-1"></i> Lihat Pelanggan
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pelanggan Perusahaan -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Pelanggan Perusahaan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($companyCustomers ?? 0, 0, ',', '.') }}</div>
                                @if (($companyCustomers ?? 0) == 0)
                                    <div class="text-muted">Tidak ada pelanggan perusahaan.</div>
                                @else
                                    <a href="{{ route('admin.mitra.index') }}" class="btn btn-primary btn-sm btn-access">
                                        <i class="fas fa-building mr-1"></i> Lihat Perusahaan
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row: Transaksi -->
    <div class="mb-4">
        <h4 class="section-title">Transaksi</h4>
        <div class="row">
            <!-- Peminjaman Berlangsung -->
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Peminjaman Berlangsung
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ongoingLoans ?? 0, 0, ',', '.') }}</div>
                                @if (($ongoingLoans ?? 0) == 0)
                                    <div class="text-muted">Tidak ada peminjaman berlangsung.</div>
                                @else
                                    <a href="{{ route('pengembalian.index') }}" class="btn btn-primary btn-sm btn-access">
                                        <i class="fas fa-undo mr-1"></i> Lihat Pengembalian
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck-moving fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection