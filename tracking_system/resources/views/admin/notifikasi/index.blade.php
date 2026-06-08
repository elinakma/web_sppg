@extends('layouts.admin')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}"
                           class="text-decoration-none fw-semibold" style="color:#133b84;">
                            <i class="bi bi-house me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Notifikasi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
                 style="border-bottom:1px solid #e8ecf0; background:#f8f9fc; border-radius:12px 12px 0 0;">
                <div>
                    <h5 class="fw-bold mb-0" style="color:#133b84;">
                        <i class="bi bi-bell me-2"></i>Semua Notifikasi
                    </h5>
                    <p class="text-muted small mb-0">
                        Total {{ $notifikasi->total() }} notifikasi
                    </p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success m-3 mb-0">{{ session('success') }}</div>
            @endif

            {{-- Daftar Notifikasi --}}
            @forelse($notifikasi as $n)
            @php
            $ikonMap = [
                'pengiriman_selesai' => ['bi-check-circle-fill', '#16a34a'],
                'perjalanan_selesai' => ['bi-truck',             '#2563eb'],
            ];
            [$icon, $color] = $ikonMap[$n->tipe] ?? ['bi-bell-fill', '#6b7280'];
            $targetUrl = $n->url ?? '#';
            @endphp
            <div class="notif-row d-flex gap-3 px-4 py-3 align-items-start"
                data-id="{{ $n->id }}"
                data-url="{{ $targetUrl }}"
                data-read="{{ $n->dibaca ? 'true' : 'false' }}"
                style="border-bottom:1px solid #f0f2f5;
                    background:{{ $n->dibaca ? '#fff' : '#f0f5ff' }};
                    cursor: {{ $targetUrl !== '#' ? 'pointer' : 'default' }};
                    transition: background .15s;"
                onmouseenter="this.style.background='#e8f0fe'"
                onmouseleave="this.style.background='{{ $n->dibaca ? '#fff' : '#f0f5ff' }}'">

            {{-- Ikon --}}
            <div style="width:40px;height:40px;border-radius:12px;
                        background:{{ $color }}18;flex-shrink:0;
                        display:flex;align-items:center;justify-content:center;">
                <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:1.1rem;"></i>
            </div>

            {{-- Konten --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold" style="font-size:.88rem;color:#1a202c;">
                            {{ $n->judul }}
                        </span>
                        @if(!$n->dibaca)
                            <span class="badge ms-1 text-white"
                                    style="background:#133b84;font-size:.65rem;border-radius:20px;">
                                Baru
                            </span>
                        @endif
                    </div>
                    <span class="text-muted" style="font-size:.72rem;white-space:nowrap;">
                        {{ $n->waktuRelatif() }}
                    </span>
                </div>

                <p class="text-muted mb-1" style="font-size:.82rem;line-height:1.5;">
                    {{ $n->pesan }}
                </p>

                <div class="d-flex align-items-center gap-3 flex-wrap"
                        style="font-size:.75rem;color:#9ca3af;">
                    @if($n->pengirim)
                        <span><i class="bi bi-person-fill me-1"></i>{{ $n->pengirim->name }}</span>
                    @endif
                    <span>
                        <i class="bi bi-clock me-1"></i>
                        {{ $n->created_at->locale('id')->isoFormat('dddd, D MMM Y · HH:mm') }}
                    </span>
                    @if($n->url && $n->url !== '#')
                        <span class="text-primary" style="font-size:.75rem;">
                            <i class="bi bi-arrow-right-circle me-1"></i>Klik untuk lihat detail
                        </span>
                    @endif
                </div>
            </div>
            </div>
            @empty
            <div class="text-center py-5">
            <i class="bi bi-bell-slash text-muted" style="font-size:3rem;opacity:.3;"></i>
            <p class="text-muted mt-3 mb-0">Belum ada notifikasi.</p>
            </div>
            @endforelse

        </div>
    </div>

    {{-- Pagination --}}
    @if($notifikasi->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $notifikasi->links() }}
    </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    document.querySelectorAll('.notif-row').forEach(row => {
        row.addEventListener('click', async () => {
            const id     = row.dataset.id;
            const url    = row.dataset.url;
            const isRead = row.dataset.read === 'true';

            if (!isRead) {
                try {
                    await fetch(`/admin/notifikasi/${id}/baca`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                    });
                    row.style.background = '#fff';
                    row.dataset.read = 'true';
                    row.querySelector('.badge')?.remove();
                } catch(e) { console.error(e); }
            }

            // Navigasi ke URL jika ada
            if (url && url !== '#') {
                window.location.href = url;
            }
        });
    });
});
</script>
@endsection