<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->redirectPathForUser(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        AuditLog::record(
            'login',
            "Pengguna {$user->name} ({$user->username}) berhasil masuk ke sistem.",
            $user,
            ['role' => $user->role->value, 'username' => $user->username],
            $user
        );

        $targetUrl = $this->redirectPathForUser($user);

        return redirect()->intended($targetUrl)
            ->with('success', "Selamat datang kembali, {$user->name}!");
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::record(
                'logout',
                "Pengguna {$user->name} ({$user->username}) keluar dari sistem.",
                $user,
                ['role' => $user->role->value, 'username' => $user->username],
                $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem CBT School.');
    }

    /**
     * Determine role-based redirect target URL for the authenticated user.
     */
    public function redirectPathForUser(User $user): string
    {
        return match ($user->role) {
            UserRole::SUPERADMIN, UserRole::ADMIN => route('admin.dashboard'),
            UserRole::TEACHER => route('teacher.dashboard'),
            UserRole::STUDENT => route('student.dashboard'),
            default => route('dashboard'),
        };
    }
}
