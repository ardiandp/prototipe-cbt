<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dispatch user to their role-specific dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            UserRole::SUPERADMIN, UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::TEACHER => redirect()->route('teacher.dashboard'),
            UserRole::STUDENT => redirect()->route('student.dashboard'),
            default => view('adminlte-demo', ['user' => $user]),
        };
    }

    /**
     * Admin & Super Admin Dashboard.
     */
    public function admin(Request $request): View
    {
        return view('adminlte-demo', [
            'user' => $request->user(),
            'role_title' => 'Dashboard Administrator',
        ]);
    }

    /**
     * Teacher Dashboard.
     */
    public function teacher(Request $request): View
    {
        return view('adminlte-demo', [
            'user' => $request->user(),
            'role_title' => 'Dashboard Guru Pengampu',
        ]);
    }

    /**
     * Student Dashboard.
     */
    public function student(Request $request): View
    {
        return view('adminlte-demo', [
            'user' => $request->user(),
            'role_title' => 'Dashboard Siswa Peserta Ujian',
        ]);
    }
}
