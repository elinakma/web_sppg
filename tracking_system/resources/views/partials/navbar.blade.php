<nav class="navbar bg-white shadow-sm px-4 py-3">
    <button class="btn btn-light border-0" id="toggleSidebar">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-2">
        <!-- Notifikasi -->
        <div class="position-relative" id="notifWrapper">
 
            {{-- Tombol bell --}}
            <button class="btn btn-light border-0 position-relative p-2"
                    id="notifToggle"
                    type="button"
                    title="Notifikasi"
                    style="border-radius:10px;">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                {{-- Badge jumlah belum dibaca --}}
                <span id="notifBadge"
                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-white d-none"
                      style="background:#e53e3e; font-size:.65rem; min-width:18px; padding:2px 5px;">
                    0
                </span>
            </button>
 
            {{-- Panel dropdown notifikasi --}}
            <div id="notifPanel"
                 class="d-none shadow-lg"
                 style="
                    position:absolute;
                    top:calc(100% + 10px);
                    right:0;
                    width:380px;
                    max-width:95vw;
                    background:#fff;
                    border-radius:16px;
                    border:1px solid #e8ecf0;
                    z-index:9999;
                    overflow:hidden;
                 ">
 
                {{-- Header panel --}}
                <div class="d-flex align-items-center justify-content-between px-4 py-3"
                     style="background:#f8f9fc; border-bottom:1px solid #e8ecf0;">
                    <div>
                        <span class="fw-bold" style="color:#133b84; font-size:.95rem;">Notifikasi</span>
                        <span id="notifCountLabel"
                              class="ms-2 badge text-white"
                              style="background:#133b84; font-size:.7rem; border-radius:20px; padding:2px 8px; display:none;">
                            0 baru
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm text-muted p-0"
                                id="bacaSemuaBtn"
                                style="font-size:.78rem;"
                                title="Tandai semua dibaca">
                            <i class="bi bi-check2-all me-1"></i>Tandai baca
                        </button>
                    </div>
                </div>
 
                {{-- Daftar notifikasi --}}
                <div id="notifList"
                     style="max-height:380px; overflow-y:auto; overscroll-behavior:contain;">
                    {{-- Loading state --}}
                    <div id="notifLoading" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                        <p class="text-muted small mt-2 mb-0">Memuat...</p>
                    </div>
                </div>
 
                {{-- Footer panel --}}
                <div class="text-center py-2 px-3"
                     style="border-top:1px solid #e8ecf0; background:#f8f9fc;">
                    <a href="{{ route('admin.notifikasi.index') }}"
                       class="btn btn-sm w-100 fw-semibold"
                       style="color:#133b84; font-size:.82rem; border:1px solid #d0d8ec; border-radius:8px;">
                        <i class="bi bi-arrow-right-circle me-1"></i> Lihat Semua Notifikasi
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" 
                    type="button" 
                    id="profileDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="fw-semibold text-dark d-none d-md-inline">
                    {{ Auth::user()->name }}
                </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person-gear me-2"></i> Edit Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
(function () {
    'use strict';
 
    // ── Konfigurasi ───────────────────────────────────────────────────
    const DROPDOWN_URL   = "{{ route('admin.notifikasi.dropdown') }}";
    const BACA_SEMUA_URL = "{{ route('admin.notifikasi.baca-semua') }}";
    const BACA_URL       = (id) => `/admin/notifikasi/${id}/baca`;
    const HAPUS_URL      = (id) => `/admin/notifikasi/${id}`;
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const POLL_INTERVAL  = 30_000; // 30 detik
 
    // ── Elemen ────────────────────────────────────────────────────────
    const toggle        = document.getElementById('notifToggle');
    const panel         = document.getElementById('notifPanel');
    const badge         = document.getElementById('notifBadge');
    const countLabel    = document.getElementById('notifCountLabel');
    const listEl        = document.getElementById('notifList');
    const loadingEl     = document.getElementById('notifLoading');
    const bacaSemuaBtn  = document.getElementById('bacaSemuaBtn');
 
    let panelOpen   = false;
    let lastFetch   = null;
 
    // ── Ikon per tipe notifikasi ──────────────────────────────────────
    function ikonTipe(tipe) {
        switch (tipe) {
            case 'pengiriman_selesai': return { icon: 'bi-check-circle-fill', color: '#16a34a' };
            case 'perjalanan_selesai': return { icon: 'bi-truck',             color: '#2563eb' };
            default:                  return { icon: 'bi-bell-fill',          color: '#6b7280' };
        }
    }
 
    // ── Render satu item notifikasi ───────────────────────────────────
    function buatItem(n) {
        const { icon, color } = ikonTipe(n.tipe);
        const bgItem = n.dibaca ? '#fff' : '#f0f5ff';
        const dotHtml = n.dibaca
            ? ''
            : `<span style="width:8px;height:8px;border-radius:50%;background:#133b84;display:inline-block;flex-shrink:0;margin-top:5px;"></span>`;
 
        return `
        <div class="notif-item d-flex gap-3 px-4 py-3 align-items-start"
             data-id="${n.id}"
             data-read="${n.dibaca}"
             style="background:${bgItem}; border-bottom:1px solid #f0f2f5; cursor:pointer; transition:background .15s;"
             onmouseenter="this.style.background='#e8f0fe'"
             onmouseleave="this.style.background='${bgItem}'">
 
            {{-- Icon tipe --}}
            <div style="width:36px;height:36px;border-radius:10px;background:${color}18;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi ${icon}" style="color:${color};font-size:.95rem;"></i>
            </div>
 
            {{-- Konten --}}
            <div class="flex-grow-1 notif-content" style="min-width:0;">
                <div class="fw-semibold" style="font-size:.82rem;color:#1a202c;line-height:1.3;">
                    ${escHtml(n.judul)}
                </div>
                <div class="text-muted mt-1" style="font-size:.78rem;line-height:1.4;word-break:break-word;">
                    ${escHtml(n.pesan)}
                </div>
                <div class="mt-1 d-flex align-items-center gap-2" style="font-size:.72rem;color:#9ca3af;">
                    <i class="bi bi-person-fill"></i>
                    <span>${escHtml(n.pengirim)}</span>
                    <span>·</span>
                    <span>${escHtml(n.waktu)}</span>
                </div>
            </div>
 
            {{-- Titik belum dibaca + hapus --}}
            <div class="d-flex flex-column align-items-center gap-1" style="flex-shrink:0;">
                ${dotHtml}
                <button class="btn p-0 hapus-notif-btn"
                        data-id="${n.id}"
                        title="Hapus"
                        style="opacity:.35;transition:opacity .15s;line-height:1;"
                        onmouseenter="this.style.opacity='1'"
                        onmouseleave="this.style.opacity='.35'">
                    <i class="bi bi-x" style="font-size:.85rem;color:#6b7280;"></i>
                </button>
            </div>
        </div>`;
    }
 
    // ── Escape HTML ───────────────────────────────────────────────────
    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
 
    // ── Fetch & render dropdown ───────────────────────────────────────
    async function fetchNotif(tampilLoading = false) {
        if (tampilLoading) {
            loadingEl.style.display = 'block';
            listEl.querySelectorAll('.notif-item, .notif-empty').forEach(el => el.remove());
        }
 
        try {
            const res  = await fetch(DROPDOWN_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            lastFetch  = data;
 
            renderBadge(data.belum_dibaca);
            if (panelOpen) renderList(data.notifikasi);
 
        } catch (e) {
            console.error('Notif fetch error', e);
        } finally {
            loadingEl.style.display = 'none';
        }
    }
 
    // ── Render badge angka ─────────────────────────────────────────────
    function renderBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
            countLabel.textContent = `${count} baru`;
            countLabel.style.display = '';
        } else {
            badge.classList.add('d-none');
            countLabel.style.display = 'none';
        }
    }
 
    // ── Render daftar notif di panel ──────────────────────────────────
    function renderList(notifikasi) {
        listEl.querySelectorAll('.notif-item, .notif-empty').forEach(el => el.remove());
 
        if (!notifikasi || notifikasi.length === 0) {
            listEl.insertAdjacentHTML('beforeend', `
                <div class="notif-empty text-center py-5 px-3">
                    <i class="bi bi-bell-slash text-muted" style="font-size:2rem;opacity:.4;"></i>
                    <p class="text-muted small mt-2 mb-0">Belum ada notifikasi</p>
                </div>`);
            return;
        }
 
        notifikasi.forEach(n => {
            listEl.insertAdjacentHTML('beforeend', buatItem(n));
        });
 
        // Event klik item → buka URL + tandai baca
        listEl.querySelectorAll('.notif-item').forEach(el => {
            el.querySelector('.notif-content')?.addEventListener('click', async () => {
                const id   = el.dataset.id;
                const isRead = el.dataset.read === 'true';
                const n    = lastFetch?.notifikasi?.find(x => String(x.id) === id);
 
                if (!isRead) await tandaiBaca(id);
                if (n?.url && n.url !== '#') window.location.href = n.url;
            });
        });
 
        // Event klik hapus
        listEl.querySelectorAll('.hapus-notif-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                await hapusNotif(btn.dataset.id);
                btn.closest('.notif-item').remove();
                await fetchNotif();
            });
        });
    }
 
    // ── Tandai satu notif dibaca ──────────────────────────────────────
    async function tandaiBaca(id) {
        try {
            await fetch(BACA_URL(id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            await fetchNotif();
        } catch (e) { console.error(e); }
    }
 
    // ── Hapus satu notif ──────────────────────────────────────────────
    async function hapusNotif(id) {
        try {
            await fetch(HAPUS_URL(id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
        } catch (e) { console.error(e); }
    }
 
    // ── Toggle panel ──────────────────────────────────────────────────
    toggle.addEventListener('click', async (e) => {
        e.stopPropagation();
        panelOpen = !panelOpen;
        panel.classList.toggle('d-none', !panelOpen);
 
        if (panelOpen) {
            await fetchNotif(true);
            if (lastFetch) renderList(lastFetch.notifikasi);
        }
    });
 
    // ── Klik di luar → tutup panel ────────────────────────────────────
    document.addEventListener('click', (e) => {
        if (panelOpen && !document.getElementById('notifWrapper').contains(e.target)) {
            panelOpen = false;
            panel.classList.add('d-none');
        }
    });
 
    // ── Baca semua ────────────────────────────────────────────────────
    bacaSemuaBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        try {
            await fetch(BACA_SEMUA_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            await fetchNotif(true);
            if (lastFetch) renderList(lastFetch.notifikasi);
        } catch (err) { console.error(err); }
    });
 
    // ── Polling otomatis tiap 30 detik ────────────────────────────────
    fetchNotif(); // fetch pertama saat halaman load
    setInterval(fetchNotif, POLL_INTERVAL);
 
})();
</script>