<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }
        .login-card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            background: white;
        }
        .login-header {
            background: url('/assets/images/bg-mbg.png') no-repeat center center;
            background-size: cover;
            height: 160px;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .login-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        .login-header h4 {
            position: relative;
            color: white;
            font-weight: 700;
            font-size: 1.4rem;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            margin-bottom: 16px;
            z-index: 1;
            padding: 0 12px;
            line-height: 1.3;
            word-break: break-word;
        }
        .login-body {
            padding: 28px 20px;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            font-size: 0.95rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
        }
        .btn-primary {
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.25s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13,110,253,.2);
        }
        .form-check-label, .forgot-link {
            font-size: 0.9rem;
        }
        .footer-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 1.25rem;
            text-align: center;
        }

        @media (max-width: 576px) {
            .login-wrapper {
                padding: 10px;
            }
            .login-card {
                border-radius: 12px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            }
            .login-header {
                height: 140px;
            }
            .login-header h4 {
                font-size: 1.25rem;
                margin-bottom: 12px;
            }
            .login-body {
                padding: 24px 18px;
            }
            .form-control {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            .btn-primary {
                padding: 10px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h4>Sistem Distribusi <br>Makan Bergizi Gratis</h4>
            </div>
            <div class="login-body">
                <!-- Pesan error -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="admin@email.com" required autofocus value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        <a href="{{ route('password.request') }}"  class="forgot-link">Lupa password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>
        <p class="footer-text">
            © {{ date('Y') }} SPPG Dahlia - Geneng, Tambakromo, Ngawi.
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>