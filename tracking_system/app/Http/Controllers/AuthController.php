<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada dan statusnya Nonaktif
        if ($user && $user->status === 'Nonaktif') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'],
            ]);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
        $user = Auth::user();

        // Restrict Role untuk Web
        if (in_array($user->role, ['Driver', 'Aslap'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ["Akun {$user->role} hanya dapat login melalui aplikasi mobile."],
            ]);
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'Admin'    => redirect()->route('admin.dashboard'),
            'Gizi'     => redirect()->route('gizi.dashboard'),
            'Akuntan'  => redirect()->route('akuntan.dashboard'),
            default    => redirect('/'),
        };
    }

        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 
                'Kami telah mengirimkan email terkait reset kata sandi Anda. Silakan cek pada akun email yang telah anda masukkan.'
            );
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Profile
    public function showProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Update Profile (semua role)
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'telepon'  => 'nullable|string|regex:/^[89][0-9]{8,12}$/|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name    = $request->name;
        $user->telepon = $request->telepon;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.edit')
                        ->with('success', 'Profil berhasil diperbarui.');
    }
}
