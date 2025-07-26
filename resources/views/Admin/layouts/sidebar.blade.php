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
</style>

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
        </div>
        <div class="sidebar-brand-text mx-3" style="font-size: 13px;">Laris Jaya Gas</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>

    @if(Auth::check() && Auth::user()->role->nama_role === 'administrator')
        <hr class="sidebar-divider">

        <!-- Master Data Heading -->
        <div class="sidebar-heading">Master Data</div>

        <!-- Data Pelanggan -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse"
               data-target="#collapseDataPelanggan" aria-expanded="false"
               aria-controls="collapseDataPelanggan">
                <i class="fas fa-fw fa-users"></i>
                <span>Data Pelanggan</span>
            </a>
            <div id="collapseDataPelanggan" class="collapse"
                 aria-labelledby="headingDataPelanggan" data-parent="#accordionSidebar">
                <div class="collapse-inner rounded py-2">
                    <a class="collapse-item" href="{{ route('admin.orang.index') }}">
                        <i class="fas fa-user mr-2"></i> Data Perorangan
                    </a>
                    <a class="collapse-item" href="{{ route('admin.perusahaan.index') }}">
                        <i class="fas fa-building mr-2"></i> Data Perusahaan
                    </a>
                    <a class="collapse-item" href="{{ route('admin.orang_perusahaan.index') }}">
                        <i class="fas fa-handshake mr-2"></i> Relasi Orang-Perusahaan
                    </a>
                </div>
            </div>
        </li>

        <!-- Pengelolaan Akun -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse"
               data-target="#collapsePengelolaanAkun" aria-expanded="false"
               aria-controls="collapsePengelolaanAkun">
                <i class="fas fa-fw fa-user-cog"></i>
                <span>Pengelolaan Akun</span>
            </a>
            <div id="collapsePengelolaanAkun" class="collapse"
                 aria-labelledby="headingPengelolaanAkun" data-parent="#accordionSidebar">
                <div class="collapse-inner rounded py-2">
                    <a class="collapse-item" href="{{ route('admin.akun.index') }}">
                        <i class="fas fa-user-cog mr-2"></i> Data Akun
                    </a>
                    <a class="collapse-item" href="{{ route('admin.role.index') }}">
                        <i class="fas fa-user-shield mr-2"></i> Data Role
                    </a>
                </div>
            </div>
        </li>

        <!-- Data Tabung -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse"
               data-target="#collapseDataTabung" aria-expanded="false"
               aria-controls="collapseDataTabung">
                <i class="fas fa-fw fa-cube"></i>
                <span>Data Tabung</span>
            </a>
            <div id="collapseDataTabung" class="collapse"
                 aria-labelledby="headingDataTabung" data-parent="#accordionSidebar">
                <div class="collapse-inner rounded py-2">
                    <a class="collapse-item" href="{{ route('admin.jenis_tabung.index') }}">
                        <i class="fas fa-gas-pump mr-2"></i> Jenis Tabung
                    </a>
                    <a class="collapse-item" href="{{ route('admin.status_tabung.index') }}">
                        <i class="fas fa-toggle-on mr-2"></i> Status Tabung
                    </a>
                    <a class="collapse-item" href="{{ route('admin.tabung.index') }}">
                        <i class="fas fa-cubes mr-2"></i> Ketersediaan Tabung
                    </a>
                </div>
            </div>
        </li>

        <!-- Data Notifikasi -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse"
               data-target="#collapseDataNotifikasi" aria-expanded="false"
               aria-controls="collapseDataNotifikasi">
                <i class="fas fa-fw fa-bell"></i>
                <span>Data Notifikasi</span>
            </a>
            <div id="collapseDataNotifikasi" class="collapse"
                 aria-labelledby="headingDataNotifikasi" data-parent="#accordionSidebar">
                <div class="collapse-inner rounded py-2">
                    <a class="collapse-item" href="#">
                        <i class="fas fa-file-alt mr-2"></i> Template Notifikasi
                    </a>
                    <a class="collapse-item" href="#">
                        <i class="fas fa-bell mr-2"></i> Notifikasi
                    </a>
                </div>
            </div>
        </li>
    @endif

    <hr class="sidebar-divider">

    <!-- Master Transaksi Heading -->
    <div class="sidebar-heading">Master Transaksi</div>

    <!-- Peminjaman -->
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-truck-moving"></i>
            <span>Peminjaman</span>
        </a>
    </li>

    <!-- Pengembalian -->
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-undo-alt"></i>
            <span>Pengembalian</span>
        </a>
    </li>

    <!-- Pengisian -->
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-fill-drip"></i>
            <span>Pengisian</span>
        </a>
    </li>

    <!-- Tagihan -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse"
           data-target="#collapseTagihan" aria-expanded="false"
           aria-controls="collapseTagihan">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Tagihan</span>
        </a>
        <div id="collapseTagihan" class="collapse"
             aria-labelledby="headingTagihan" data-parent="#accordionSidebar">
            <div class="collapse-inner rounded py-2">
                <a class="collapse-item" href="#">
                    <i class="fas fa-times-circle mr-2"></i> Belum Lunas
                </a>
                <a class="collapse-item" href="#">
                    <i class="fas fa-check-circle mr-2"></i> Sudah Lunas
                </a>
                <a class="collapse-item" href="#">
                    <i class="fas fa-money-check-alt mr-2"></i> Pembayaran Tagihan
                </a>
            </div>
        </div>
    </li>

    <!-- Deposit -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse"
           data-target="#collapseDeposit" aria-expanded="false"
           aria-controls="collapseDeposit">
            <i class="fas fa-fw fa-wallet"></i>
            <span>Deposit</span>
        </a>
        <div id="collapseDeposit" class="collapse"
             aria-labelledby="headingDeposit" data-parent="#accordionSidebar">
            <div class="collapse-inner rounded py-2">
                <a class="collapse-item" href="#">
                    <i class="fas fa-wallet mr-2"></i> Saldo Deposit
                </a>
                <a class="collapse-item" href="#">
                    <i class="fas fa-history mr-2"></i> Riwayat Deposit
                </a>
            </div>
        </div>
    </li>

    <!-- Denda -->
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-exclamation-circle"></i>
            <span>Denda</span>
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

</ul>
<!-- End of Sidebar -->