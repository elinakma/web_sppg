<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @yield('styles')
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #fff;
            border-right: 1px solid #dee2e6;
            z-index: 1000;
            overflow-y: auto;
        }
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1020;
        }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex min-vh-100">
        <!-- Sidebar (fixed left) -->
        @include('partials.sidebar')

        <!-- Main content area -->
        <div class="flex-grow-1 d-flex flex-column" style="margin-left:250px;">
            <!-- Navbar (top) -->
            @include('partials.navbar')

            <!-- Page content -->
            <main class="flex-grow-1 p-4">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>