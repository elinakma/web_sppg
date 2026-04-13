@extends('layouts.admin')

@section('title', 'Dashboard Admin')

<style>
    .welcome-box {
        background: #f8f9fa;
        border-left: 5px solid #0d6efd;
        padding: 15px 20px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .welcome-box p {
        margin: 0;
        font-size: 17px;
        line-height: 1.6;
    }

    .welcome-box strong {
        color: #0d6efd;
    }

    .app-name {
        font-weight: 600;
        color: #198754;
    }

    .role-admin {
        background: #0d6efd;
        color: #fff;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 13px;
    }

</style>

@section('content')

<div class="welcome-box">
    <p>
        Selamat datang <strong>{{ Auth::user()->name }}</strong> di 
        <span class="app-name">Sistem Distribusi SPPG Geneng!</span> Anda login sebagai <span class="role-admin">Kepala SPPG</span>
    </p>
</div>

<div class="row g-4">

    <!-- Jumlah Pengguna -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 text-primary fs-2">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-muted">Jumlah Pengguna</h6>
                    <h3 class="fw-bold mb-0">{{ $jumlahPengguna ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Jumlah Sekolah Aktif -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 text-success fs-2">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-muted">Jumlah Sekolah Aktif</h6>
                    <h3 class="fw-bold mb-0">{{ $jumlahSekolah ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribusi Hari Ini -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 text-warning fs-2">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-muted">Distribusi Hari Ini</h6>
                    <h3 class="fw-bold mb-0">{{ $distribusiHariIni ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
