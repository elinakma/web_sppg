@extends('layouts.admin')

@section('title', 'Dashboard Admin')

<style>
    .welcome-card {
        background: rgba(37,99,235,0.08);
        border-radius: 10px;
        border: none;
    }
    .welcome-card .icon-box {
        font-size: 20px;
        padding: 8px;
        border-radius: 50%;
        background: rgba(37,99,235,0.12);
        color: #2563eb;
        margin-right: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .welcome-card .text-box {
        display: flex;
        flex-direction: row;
        align-items: baseline;
        gap: 12px;
        flex-wrap: wrap;
    }

    .welcome-card h6 {
        margin: 0;
        font-weight: 500;
        color: #133b84;
        font-size: 15px;
        line-height: 1.4;
    }

    .welcome-card p {
        margin: 0;
        font-size: 15px;
        color: #374151;
        line-height: 1.4;
    }

    .start-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .start-card .icon-box {
        font-size: 18px;          /* kecil */
        padding: 10px;            /* tipis */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
    }

    .start-card h6 {
        margin: 0;
        font-size: 14px;
        color: #6c757d;           /* muted */
        font-weight: 500;
    }

    .start-card h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #111827;
    }


</style>

@section('content')

<div class="card mb-4 shadow-sm welcome-card">
    <div class="card-body d-flex align-items-center">
        <!-- Ikon -->
        <div class="icon-box">
            <i class="bi bi-speedometer2"></i>
        </div>
        <!-- Teks horizontal -->
        <div class="text-box">
            <h6 class="fw-semibold">Selamat Datang</h6>
            <p>
                <strong>{{ Auth::user()->name }}</strong> di 
                <span class="fw-semibold text-success">Sistem Distribusi SPPG Geneng</span> — 
                Anda login sebagai <span class="badge role-badge role-admin">Kepala SPPG</span>
            </p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Jumlah Pengguna -->
    <div class="col-md-4">
        <div class="card start-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h6>Jumlah Pengguna</h6>
                    <h3>{{ $jumlahPengguna ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Jumlah Sekolah Aktif -->
    <div class="col-md-4">
        <div class="card start-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-success bg-opacity-10 text-success">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h6>Jumlah Sekolah Aktif</h6>
                    <h3>{{ $jumlahSekolah ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribusi Hari Ini -->
    <div class="col-md-4">
        <div class="card start-card">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <h6>Distribusi Hari Ini</h6>
                    <h3>{{ $distribusiHariIni ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
