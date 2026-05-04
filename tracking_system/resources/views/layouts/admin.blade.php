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
            min-width: 90px;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #fff;
            white-space: nowrap;
        }
        .role-admin {
            background: #0d6efd;
            color: #fff;
        }
        .role-akuntan {
            background: #198754;
            color: #fff;
        }
        .role-aslap {
            background: #495057;
            color: #fff;
        }
        .role-gizi {
            background: #e86fcc;
            color: #fff;
        }
        .role-driver {
            background: #fd7e14;
            color: #fff;
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

        /* Overlay Sukses */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);

            display: flex;
            justify-content: center;
            align-items: center;

            z-index: 1055;

            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
        }

        .success-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .success-card {
            background: #fff;
            padding: 32px 40px;
            border-radius: 16px;
            text-align: center;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);

            transform: scale(.85);
            opacity: 0;
            transition: all .3s ease;
        }

        .success-overlay.show .success-card {
            transform: scale(1);
            opacity: 1;
        }

        .success-overlay.hide {
            opacity: 0;
            visibility: hidden;
        }

        .success-overlay.hide .success-card {
            transform: scale(.85);
            opacity: 0;
        }

        .success-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #20c997;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .success-icon i {
            font-size: 36px;
            color: white;
        }

        .success-overlay,
        .confirm-overlay {
            transition: opacity .25s ease, visibility .25s ease;
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

        /* Overlay Hapus */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);

            display: flex;
            justify-content: center;
            align-items: center;

            z-index: 1055;

            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
        }

        .confirm-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .confirm-card {
            background: #fff;
            padding: 32px 40px;
            border-radius: 16px;
            text-align: center;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);

            transform: scale(.85);
            opacity: 0;
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .confirm-overlay.show .confirm-card {
            transform: scale(1);
            opacity: 1;
        }

        .confirm-overlay.hide {
            opacity: 0;
            visibility: hidden;
        }

        .confirm-overlay.hide .confirm-card {
            transform: scale(.85);
            opacity: 0;
        }

        .success-card,
        .confirm-card {
            transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Icon */
        .confirm-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .confirm-icon i {
            font-size: 36px;
            color: white;
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