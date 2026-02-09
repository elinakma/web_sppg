<aside class="sidebar">
    <div class="d-flex flex-column p-3 text-white bg-dark vh-100" style="width: 260px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 25px;">
            <img src="/assets/images/logo-sppg.png" alt="Logo SPPG" style="width: 70px; height: auto;">
            <span style="font-size: 16px; font-weight: bold;">
                BADAN GIZI <br>NASIONAL
            </span>
        </div>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" 
                class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Beranda
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.pengguna.index') }}" 
                class="nav-link text-white {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge me-2"></i> Kelola Pengguna
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.pagu.index') }}" 
                class="nav-link text-white {{ request()->routeIs('admin.pagu.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i> Kelola Pagu
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.sekolah.index') }}" 
                class="nav-link text-white {{ request()->routeIs('admin.sekolah.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i> Kelola Sekolah
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('distribusi.index') }}" 
                class="nav-link text-white {{ request()->routeIs('distribusi.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data me-2"></i> Kelola Distribusi
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.map.index') }}" class="nav-link text-white">
                    <i class="bi bi-pin-map me-2"></i> Pemantauan Distribusi
                </a>
            </li>

            <li class="nav-item mt-3">
                <hr class="text-secondary">
            </li>
        </ul>
    </div>
</aside>
