<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-family: 'Segoe UI', sans-serif; }
        .login-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 15px; }
        .login-card { border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.12); overflow: hidden; max-width: 420px; background: white; }
        .login-header { background: url('/assets/images/bg-mbg.png') no-repeat center; background-size: cover; height: 140px; position: relative; }
        .login-header::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.45); }
        .login-header h4 { position: relative; color: white; text-align: center; margin-bottom: 0; padding-top: 50px; z-index: 1; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h4>Reset Password</h4>
            </div>
            <div class="login-body p-4">
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" required>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>