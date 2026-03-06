<aside id="sidebar" class="sidebar">
    <div class="p-4 text-white">

        <div class="d-flex align-items-center gap-3 mb-1">
            <img src="/assets/images/logo-sppg.png" width="48" alt="Logo">
            <div class="sidebar-title fw-bold fs-5">
                BADAN GIZI <br> NASIONAL
            </div>
        </div>
        <hr class="mb-4 border-white">

        <ul class="nav nav-pills flex-column gap-2">
            <!-- Menu Umum -->
            <li>
                <a href="{{ route(strtolower(Auth::user()->role) . '.dashboard') }}"
                   class="nav-link {{ request()->routeIs(Auth::user()->role . '.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <!-- Menu Admin -->
            @if (Auth::user()->isAdmin())
                <li>
                    <a href="{{ route('admin.pengguna.index') }}"
                       class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i>
                        <span class="menu-text">Kelola Pengguna</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.pagu.index') }}"
                       class="nav-link {{ request()->routeIs('admin.pagu.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin me-2"></i>
                        <span class="menu-text">Kelola Pagu</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.sekolah.index') }}"
                       class="nav-link {{ request()->routeIs('admin.sekolah.*') ? 'active' : '' }}">
                        <i class="bi bi-building me-2"></i>
                        <span class="menu-text">Kelola Sekolah</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.distribusi.index') }}"
                       class="nav-link {{ request()->routeIs('admin.distribusi.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data me-2"></i>
                        <span class="menu-text">Kelola Distribusi</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.map.index') }}"
                       class="nav-link {{ request()->routeIs('admin.map.*') ? 'active' : '' }}">
                        <i class="bi bi-pin-map me-2"></i>
                        <span class="menu-text">Pemantauan</span>
                    </a>
                </li>

            <!-- Menu Ahli Gizi -->
            @elseif (Auth::user()->isGizi())
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-clipboard-check me-2"></i>
                        <span class="menu-text">Verifikasi Menu</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-graph-up me-2"></i>
                        <span class="menu-text">Laporan Gizi</span>
                    </a>
                </li>

            <!-- Menu Akuntan -->
            @elseif (Auth::user()->isAkuntan())
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-calculator me-2"></i>
                        <span class="menu-text">Kelola Anggaran</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                        <span class="menu-text">Laporan Keuangan</span>
                    </a>
                </li>

            <!-- Menu Aslap -->
            @elseif (Auth::user()->isAslap())
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-truck me-2"></i>
                        <span class="menu-text">Jadwal Distribusi</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-check-circle me-2"></i>
                        <span class="menu-text">Konfirmasi Pengiriman</span>
                    </a>
                </li>

            <!-- Menu Logout -->
            @endif
            <li class="mt-auto">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link text-danger w-100 text-start">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        <span class="menu-text">Logout</span>
                    </button>
                </form>
            </li>

        </ul>
    </div>
</aside>