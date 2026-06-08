<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @yield('styles')
    <style>
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #111827;
            transition: width 0.35s ease;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1040;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: #ffffff;
        }

        /* Hover */
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: #ffffff;
        }

        /* Active */
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .sidebar-title {
            display: none;
        }

        /* Center logo */
        .sidebar.collapsed .d-flex.align-items-center {
            justify-content: center;
        }

        .sidebar.collapsed .d-flex.align-items-center img {
            width: 36px;
        }

        .sidebar.collapsed .p-4 {
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed ul {
            padding: 0;
        }

        .sidebar.collapsed li {
            display: flex;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link {
            width: 44px;
            height: 44px;
            padding: 0;
            margin: 6px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .sidebar.collapsed .nav-link i {
            font-size: 18px;
            margin: 0;
        }

        .sidebar.collapsed .nav-link.active {
            background: linear-gradient(135deg, #1d4ed8);
            box-shadow: 0 4px 12px rgba(37,99,235,.35);
        }

        #sidebarOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1030;
            display: none;
        }

        #sidebarOverlay.show {
            display: block;
        }

        /* Main content geser */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            transition: margin-left 0.35s ease;
        }

        .main-wrapper.expanded {
            margin-left: 80px;
        }

        /* Mobile */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.35s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0 !important;
            }
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1020;
        }
       .action-btn {
            padding: 2px 8px;
            font-size: 12px;
            margin: 2px;
            line-height: 2;
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;       /* seragam */
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: all 0.25s ease;
        }

        .role-admin {
            background: #3b82f6;
        }

        .role-akuntan {
            background: #22c55e;
        }

        .role-aslap {
            background: #6b7280;
        }

        .role-gizi {
            background: #ec4899;
        }

        .role-driver {
            background: #f97316;
        }

        .role-badge:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .table th {
            font-weight: 500;
        }

        /* Search */
        .input-group .form-control,
        .input-group .btn {
            border-color: #6c757d;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .btn {
            border-left: 0;
        }

        .input-group .form-control:focus,
        .input-group .btn:focus {
            box-shadow: none;
            outline: none;
            border-color: #6c757d;
        }

        /* Telp */
        .input-telp {
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .input-telp:focus-within {
            border-color: #133b84;
            box-shadow: 0 0 0 2px rgba(19, 59, 132, 0.15);
        }

        .input-telp-text {
            background: #f1f3f5;
            padding: 10px 12px;
            font-weight: 500;
            color: #495057;
            border-right: 1px solid #dee2e6;
        }

        .input-telp .form-control {
            border: none;
            box-shadow: none;
            outline: none;
            padding: 10px 12px;
        }

        .input-telp .form-control:focus {
            box-shadow: none;
        }

        /* Overlay umum */
        .success-overlay,
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(4px); /* efek blur modern */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1055;
            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
        }

        .success-overlay.show,
        .confirm-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Card */
        .success-card,
        .confirm-card {
            background: #fff;
            padding: 32px 40px;
            border-radius: 18px;
            text-align: center;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 12px 32px rgba(0,0,0,.25);
            transform: translateY(20px) scale(.95);
            opacity: 0;
            transition: all .35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .success-overlay.show .success-card,
        .confirm-overlay.show .confirm-card {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        /* Icon */
        .success-icon,
        .confirm-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }

        .success-icon {
            background: linear-gradient(135deg, #20c997, #198754);
        }

        .confirm-icon {
            background: linear-gradient(135deg, #dc3545, #b02a37);
        }

        .success-icon i,
        .confirm-icon i {
            font-size: 36px;
            color: #fff;
        }

        /* Tombol */
        .confirm-card .btn,
        .success-card .btn {
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all .25s ease;
        }

        .confirm-card .btn:hover,
        .success-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,.15);
        }

        /* Overlay error */
        .error-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .error-overlay.show {
            opacity: 1;
        }

        .error-overlay.hide {
            opacity: 0;
        }

        .error-card {
            background: white;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            width: 300px;
        }

        .error-icon {
            width: 60px;
            height: 60px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            font-size: 24px;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-body {
            background: #f9fafb;
            border-radius: 0 0 18px 18px;
        }

        .modal-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #d1d5db, transparent);
            margin: 0;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #28a745;
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .btn-uniform {
            min-width: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 13px;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }
        .accordion-button {
            background: #f9fafb;
            font-weight: 600;
            border-radius: 8px;
        }
        .accordion-item {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .status-badge {
            min-width: 80px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            letter-spacing: 0.3px;
            transition: all 0.25s ease;
        }

        .status-badge:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.18);
        }

        .action-group{
            display:flex;
            justify-content:center;
            flex-wrap:wrap;
            gap:12px;
        }

        .soft-btn{
            width:34px;
            height:34px;
            border:none;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            cursor:pointer;
            transition:all 0.25s ease;
            box-shadow:0 6px 14px rgba(0,0,0,0.08);
            text-decoration:none;
            position:relative;
            overflow:hidden;
        }

        /* Click effect */
        .soft-btn:active{
            box-shadow:inset 0 3px 8px rgba(0,0,0,0.15);
        }

        /* Colors normal */
        .btn-next{
            background:#fff7db;
            color:#e0a800;
        }

        .btn-detail{
            background:#e9f2ff;
            color:#0d6efd;
        }

        .btn-print{
            background:#e8f8ee;
            color:#16a34a;
        }

        .btn-delete{
            background:#fdecec;
            color:#ef4444;
        }

        /* Hover warna lebih gelap */
        .btn-next:hover{
            background:#ffe9a8;
        }

        .btn-detail:hover{
            background:#cfe3ff;
        }

        .btn-print:hover{
            background:#cdeed8;
        }

        .btn-delete:hover{
            background:#f9caca;
        }

        /* Tooltip */
        .soft-btn::after{
            content:attr(data-title);
            position:absolute;
            bottom:-32px;
            background:#111827;
            color:#fff;
            font-size:12px;
            padding:4px 8px;
            border-radius:6px;
            opacity:0;
            transition:0.2s ease;
            white-space:nowrap;
        }

        /* Tooltip naik mendekati button */
        .soft-btn:hover::after{
            opacity:1;
            bottom:-26px;
        }

        .pagination {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 6px 12px;
            background: #fff;
        }

        .page-item .page-link {
            border: none;
            margin: 0 4px;
            border-radius: 8px;
            color: #133b84;
            font-weight: 500;
            transition: all 0.25s ease;
        }

        .page-item .page-link:hover {
            background: #133b84;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(19,59,132,0.25);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            box-shadow: 0 3px 10px rgba(37,99,235,0.35);
        }
        
        .filter-box {
            flex-wrap: nowrap;
            background: #f9fafb;
            padding: 4px 8px;
            border-radius: 50px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .filter-input {
            width: 145px;
            min-width: 145px;
            border: none;
            box-shadow: none;
            font-size: 13px;
            padding: 6px 12px;
            transition: all 0.25s ease;
        }

        .filter-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(19,59,132,0.15);
            border-color: #133b84;
        }

        .filter-box .btn {
            font-size: 13px;
            transition: all 0.25s ease;
        }

        .filter-box .btn:hover {
            box-shadow: 0 3px 8px rgba(19,59,132,0.25);
        }

        .bg-success {
            background-color: #22c55e
        }

        .bg-secondary {
            background-color: #d1d5db
        }
        
        #tambahMenuModal .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 200px);
        }

        .modal-dialog-scrollable .modal-content {
            overflow: hidden;
        }
        .btn-wa {
            background-color: #e8f5e9;
            color: #25D366;
            border: 1px solid #c8e6c9;
            transition: all 0.2s ease;
        }
        .btn-wa:hover {
            background-color: #25D366;
            color: #fff;
            border-color: #25D366;
        }
    </style>
</head>
<body class="bg-light">

    <div id="sidebarOverlay"></div>

    <div class="layout-wrapper">
        @include('partials.sidebar')

        <div class="main-wrapper">
            @include('partials.navbar')

            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
    </body>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const mainWrapper = document.querySelector(".main-wrapper");

    toggleBtn.addEventListener("click", function() {

        if (window.innerWidth < 992) {
            // MOBILE
            sidebar.classList.toggle("show");
            overlay.classList.toggle("show");
        } else {
            // DESKTOP
            sidebar.classList.toggle("collapsed");
            mainWrapper.classList.toggle("expanded");
        }

    });

    overlay.addEventListener("click", function() {
        sidebar.classList.remove("show");
        overlay.classList.remove("show");
    });

});
</script>

</html>