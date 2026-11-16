<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of system audit logs with filters and role-based scoping.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Strict protection: Students are not authorized to view audit logs
        if ($user->isStudent()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat riwayat audit.');
        }

        $query = AuditLog::with(['user', 'auditable'])->latest();

        // Scope logs: Teachers only view their own activities or logs related to their assigned exams
        $isTeacherOnly = $user->isTeacher() && ! $user->isAdmin() && ! $user->isSuperAdmin();
        if ($isTeacherOnly && $user->teacherProfile) {
            $teacherExamIds = Exam::where('teacher_id', $user->teacherProfile->id)->pluck('id');
            $attemptIds = ExamAttempt::whereIn('exam_id', $teacherExamIds)->pluck('id');
            $participantIds = ExamParticipant::whereIn('exam_id', $teacherExamIds)->pluck('id');

            $query->where(function ($q) use ($user, $teacherExamIds, $attemptIds, $participantIds) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($sub) use ($teacherExamIds) {
                        $sub->where('auditable_type', Exam::class)
                            ->whereIn('auditable_id', $teacherExamIds);
                    })
                    ->orWhere(function ($sub) use ($attemptIds) {
                        $sub->where('auditable_type', ExamAttempt::class)
                            ->whereIn('auditable_id', $attemptIds);
                    })
                    ->orWhere(function ($sub) use ($participantIds) {
                        $sub->where('auditable_type', ExamParticipant::class)
                            ->whereIn('auditable_id', $participantIds);
                    });
            });
        }

        // Action filter
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        // User filter
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Date range filter
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // Keyword search in reason, ip_address, or user name/username
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Action dictionary for filters and display
        $actionList = [
            'login' => 'Login Pengguna',
            'logout' => 'Logout Pengguna',
            'publish_exam' => 'Publikasi Jadwal Ujian',
            'unpublish_exam' => 'Tarik Jadwal (Draft)',
            'cancel_exam' => 'Batalkan Jadwal Ujian',
            'force_finish' => 'Selesaikan Paksa Peserta',
            'retake_authorization' => 'Otorisasi Ujian Ulang',
            'publish_results' => 'Rilis Pengumuman Nilai',
            'unpublish_results' => 'Tarik Pengumuman Nilai',
        ];

        // Stats summary calculation
        $baseScopeQuery = AuditLog::query();
        if ($isTeacherOnly && $user->teacherProfile) {
            $baseScopeQuery->where(function ($q) use ($user, $teacherExamIds, $attemptIds, $participantIds) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($sub) use ($teacherExamIds) {
                        $sub->where('auditable_type', Exam::class)
                            ->whereIn('auditable_id', $teacherExamIds);
                    })
                    ->orWhere(function ($sub) use ($attemptIds) {
                        $sub->where('auditable_type', ExamAttempt::class)
                            ->whereIn('auditable_id', $attemptIds);
                    })
                    ->orWhere(function ($sub) use ($participantIds) {
                        $sub->where('auditable_type', ExamParticipant::class)
                            ->whereIn('auditable_id', $participantIds);
                    });
            });
        }

        $totalScopeCount = (clone $baseScopeQuery)->count();
        $todayCount = (clone $baseScopeQuery)->whereDate('created_at', Carbon::today())->count();

        // User filter options
        $usersQuery = User::orderBy('name');
        if ($isTeacherOnly) {
            $usersQuery->where('id', $user->id);
        }
        $users = $usersQuery->get(['id', 'name', 'username', 'role']);

        return view('admin.audit_logs.index', compact(
            'logs',
            'actionList',
            'totalScopeCount',
            'todayCount',
            'users',
            'isTeacherOnly'
        ));
    }
}
