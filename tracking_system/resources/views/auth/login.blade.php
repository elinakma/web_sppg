<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <!-- Title -->
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1">Admin Login</h4>
                    <p class="text-muted mb-0">Silakan masuk untuk melanjutkan</p>
                </div>

                <!-- Error -->
                @if ($errors->any())
                    <div class="alert alert-danger small">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Form -->
                <form action="{{ route('login.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="admin@email.com"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">
                        Login
                    </button>
                </form>

            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-muted mt-3 small">
            © {{ date('Y') }} Ontoseno Trans
        </p>

    </div>
</body>
</div>
