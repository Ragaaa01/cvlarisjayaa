<style>
    .sidebar-dark {
        background-color: #002D55 !important;
    }
    .collapse-inner {
        background-color: #013B6C !important;
    }
    .sidebar .nav-link,
    .sidebar .nav-link i,
    .sidebar .collapse-item,
    .sidebar .collapse-item i {
        color: #ffffff !important;
        position: relative;
    }
    .sidebar .nav-link:hover,
    .sidebar .collapse-item:hover {
        background-color: #014A7F !important;
        color: #ffffff !important;
    }
    .sidebar .nav-link:hover i,
    .sidebar .collapse-item:hover i {
        color: #ffffff !important;
    }
    .sidebar .collapse-item.active {
        background-color: #FFCA28 !important;
        color: #002D55 !important;
        font-weight: bold;
    }
    .sidebar .collapse-item.active i {
        color: #002D55 !important;
    }
    .sidebar .nav-link.active:not(.collapse-item) {
        background-color: transparent !important;
        color: #ffffff !important;
        font-weight: bold;
    }
    .sidebar .nav-link.active:not(.collapse-item)::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 4px;
        background-color: #FFCA28;
        border-radius: 2px;
    }
    .sidebar-heading {
        color: #ffffff !important;
    }
    .sidebar-brand {
        padding: 1rem;
        background-color: #002D55 !important;
    }
    .sidebar-brand:hover {
        background-color: #014A7F !important;
    }
    .sidebar-divider {
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
    .nav-item {
        margin-bottom: 0.2rem;
    }
    .collapse-item {
        padding: 0.5rem 1rem 0.5rem 2rem !important;
    }
</style>

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar Brand -->
    @auth('web')
        @if (Auth::user()->role->nama_role === 'administrator')
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
                </div>
                <div class="sidebar-brand-text mx-3" style="font-size: 13px;">Laris Jaya Gas</div>
            </a>
        @elseif (Auth::user()->role->nama_role === 'karyawan')
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('karyawan.dashboard') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
                </div>
                <div class="sidebar-brand-text mx-3" style="font-size: 13px;">Laris Jaya Gas</div>
            </a>
        @else
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('welcome') }}">
                <div class="sidebar-brand-icon">
                    <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
                </div>
                <div class="sidebar-brand-text mx-3" style="font-size: 13px;">Laris Jaya Gas</div>
            </a>
        @endif
    @else
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('welcome') }}">
            <div class="sidebar-brand-icon">
                <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
            </div>
            <div class="sidebar-brand-text mx-3" style="font-size: 13px;">Laris Jaya Gas</div>
        </a>
    @endauth

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    @auth('web')
        @if (Auth::user()->role->nama_role === 'administrator')
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @elseif (Auth::user()->role->nama_role === 'karyawan')
            <li class="nav-item {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}" href="{{ route('karyawan.dashboard') }}">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endif
    @endauth

    @auth('web')
        @if (Auth::user()->role->nama_role === 'administrator')
            <hr class="sidebar-divider">

            <!-- Master Data Heading -->
            <div class="sidebar-heading">Master Data</div>

            @php
                $dataPelangganRoutes = ['admin.orang.index', 'admin.mitra.index', 'admin.orang_mitra.index'];
                $pengelolaanAkunRoutes = ['admin.akun.index', 'admin.role.index'];
                $dataTabungRoutes = ['admin.jenis_tabung.index', 'admin.status_tabung.index', 'admin.tabung.index', 'admin.kepemilikan_tabung.index'];
                $dataPendukungTransaksiRoutes = ['admin.jenis_transaksi.index'];
                $dataPelangganActive = collect($dataPelangganRoutes)->contains(fn($r) => request()->routeIs($r));
                $pengelolaanAkunActive = collect($pengelolaanAkunRoutes)->contains(fn($r) => request()->routeIs($r));
                $dataTabungActive = collect($dataTabungRoutes)->contains(fn($r) => request()->routeIs($r));
                $dataPendukungTransaksiActive = collect($dataPendukungTransaksiRoutes)->contains(fn($r) => request()->routeIs($r));
            @endphp

            <!-- Data Pelanggan -->
            <li class="nav-item {{ $dataPelangganActive ? 'active' : '' }}">
                <a class="nav-link {{ $dataPelangganActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                   data-target="#collapseDataPelanggan" aria-expanded="{{ $dataPelangganActive ? 'true' : 'false' }}"
                   aria-controls="collapseDataPelanggan">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Data Pelanggan</span>
                </a>
                <div id="collapseDataPelanggan" class="collapse {{ $dataPelangganActive ? 'show' : '' }}"
                     aria-labelledby="headingDataPelanggan" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded py-2">
                        <a class="collapse-item {{ request()->routeIs('admin.orang.index') ? 'active' : '' }}" href="{{ route('admin.orang.index') }}">
                            <i class="fas fa-user mr-2"></i> Data Orang
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.mitra.index') ? 'active' : '' }}" href="{{ route('admin.mitra.index') }}">
                            <i class="fas fa-building mr-2"></i> Data Mitra
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.orang_mitra.index') ? 'active' : '' }}" href="{{ route('admin.orang_mitra.index') }}">
                            <i class="fas fa-handshake mr-2"></i> Relasi Orang-Mitra
                        </a>
                    </div>
                </div>
            </li>

            <!-- Pengelolaan Akun -->
            <li class="nav-item {{ $pengelolaanAkunActive ? 'active' : '' }}">
                <a class="nav-link {{ $pengelolaanAkunActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                   data-target="#collapsePengelolaanAkun" aria-expanded="{{ $pengelolaanAkunActive ? 'true' : 'false' }}"
                   aria-controls="collapsePengelolaanAkun">
                    <i class="fas fa-fw fa-user-cog"></i>
                    <span>Pengelolaan Akun</span>
                </a>
                <div id="collapsePengelolaanAkun" class="collapse {{ $pengelolaanAkunActive ? 'show' : '' }}"
                     aria-labelledby="headingPengelolaanAkun" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded py-2">
                        <a class="collapse-item {{ request()->routeIs('admin.akun.index') ? 'active' : '' }}" href="{{ route('admin.akun.index') }}">
                            <i class="fas fa-user-cog mr-2"></i> Data Akun
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.role.index') ? 'active' : '' }}" href="{{ route('admin.role.index') }}">
                            <i class="fas fa-user-shield mr-2"></i> Data Role
                        </a>
                    </div>
                </div>
            </li>

            <!-- Data Tabung -->
            <li class="nav-item {{ $dataTabungActive ? 'active' : '' }}">
                <a class="nav-link {{ $dataTabungActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                   data-target="#collapseDataTabung" aria-expanded="{{ $dataTabungActive ? 'true' : 'false' }}"
                   aria-controls="collapseDataTabung">
                    <i class="fas fa-fw fa-cube"></i>
                    <span>Data Tabung</span>
                </a>
                <div id="collapseDataTabung" class="collapse {{ $dataTabungActive ? 'show' : '' }}"
                     aria-labelledby="headingDataTabung" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded py-2">
                        <a class="collapse-item {{ request()->routeIs('admin.jenis_tabung.index') ? 'active' : '' }}" href="{{ route('admin.jenis_tabung.index') }}">
                            <i class="fas fa-gas-pump mr-2"></i> Jenis Tabung
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.status_tabung.index') ? 'active' : '' }}" href="{{ route('admin.status_tabung.index') }}">
                            <i class="fas fa-toggle-on mr-2"></i> Status Tabung
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.tabung.index') ? 'active' : '' }}" href="{{ route('admin.tabung.index') }}">
                            <i class="fas fa-cubes mr-2"></i> Ketersediaan Tabung
                        </a>
                        <a class="collapse-item {{ request()->routeIs('admin.kepemilikan_tabung.index') ? 'active' : '' }}" href="{{ route('admin.kepemilikan_tabung.index') }}">
                            <i class="fas fa-briefcase mr-2"></i> Kepemilikan Tabung
                        </a>
                    </div>
                </div>
            </li>

            <!-- Data Pendukung Transaksi -->
            <li class="nav-item {{ $dataPendukungTransaksiActive ? 'active' : '' }}">
                <a class="nav-link {{ $dataPendukungTransaksiActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                   data-target="#collapseDataPendukungTransaksi" aria-expanded="{{ $dataPendukungTransaksiActive ? 'true' : 'false' }}"
                   aria-controls="collapseDataPendukungTransaksi">
                    <i class="fas fa-fw fa-list"></i>
                    <span>Data Pendukung Transaksi</span>
                </a>
                <div id="collapseDataPendukungTransaksi" class="collapse {{ $dataPendukungTransaksiActive ? 'show' : '' }}"
                     aria-labelledby="headingDataPendukungTransaksi" data-parent="#accordionSidebar">
                    <div class="collapse-inner rounded py-2">
                        <a class="collapse-item {{ request()->routeIs('admin.jenis_transaksi.index') ? 'active' : '' }}" href="{{ route('admin.jenis_transaksi.index') }}">
                            <i class="fas fa-list mr-2"></i> Jenis Transaksi
                        </a>
                    </div>
                </div>
            </li>
        @endif

        <hr class="sidebar-divider">

        <!-- Master Transaksi Heading -->
        <div class="sidebar-heading">Master Transaksi</div>

        @php
            $transaksiRoutes = ['transaksi.index', 'pembayaran.index', 'pengembalian.index'];
            $transaksiActive = collect($transaksiRoutes)->contains(fn($r) => request()->routeIs($r));
        @endphp

        <!-- Transaksi -->
        <li class="nav-item {{ request()->routeIs('transaksi.index') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('transaksi.index') ? 'active' : '' }}" href="{{ route('transaksi.index') }}">
                <i class="fas fa-fw fa-exchange-alt"></i>
                <span>Transaksi</span>
            </a>
        </li>

        <!-- Pembayaran -->
        <li class="nav-item {{ request()->routeIs('pembayaran.index') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('pembayaran.index') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Pembayaran</span>
            </a>
        </li>

        <!-- Pengembalian -->
        <li class="nav-item {{ request()->routeIs('pengembalian.index') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('pengembalian.index') ? 'active' : '' }}" href="{{ route('pengembalian.index') }}">
                <i class="fas fa-fw fa-undo-alt"></i>
                <span>Pengembalian</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <!-- Logout -->
        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="nav-link" style="border: none; background: none; width: 100%; text-align: left; padding: 10px 20px;">
                    <i class="fas fa-sign-out-alt fa-fw mr-2"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </li>
    @endauth
</ul>
<!-- End of Sidebar -->