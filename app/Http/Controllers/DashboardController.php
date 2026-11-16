<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
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
            default => redirect()->route('login'),
        };
    }

    /**
     * Admin & Super Admin Dashboard.
     */
    public function admin(Request $request): View
    {
        $schoolProfile = SchoolProfile::instance();
        $activeYear = AcademicYear::active();
        $activeSemester = Semester::active();

        $totalTeachers = Teacher::count();
        $totalStudents = Student::count();
        $totalClasses = Classroom::count();
        $totalDepartments = Department::count();
        $totalSubjects = Subject::count();

        $departments = Department::withCount('classrooms')->get();
        $classesByLevel = Classroom::selectRaw('level, count(*) as count')
            ->groupBy('level')
            ->orderBy('level')
            ->pluck('count', 'level');

        $recentStudents = Student::with('classroom')->latest()->take(5)->get();
        $recentTeachers = Teacher::with('subjects')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'schoolProfile',
            'activeYear',
            'activeSemester',
            'totalTeachers',
            'totalStudents',
            'totalClasses',
            'totalDepartments',
            'totalSubjects',
            'departments',
            'classesByLevel',
            'recentStudents',
            'recentTeachers'
        ));
    }

    /**
     * Teacher Dashboard.
     */
    public function teacher(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $teacher = $user->teacherProfile;

        $activeYear = AcademicYear::active();
        $activeSemester = Semester::active();

        $subjects = $teacher ? $teacher->subjects()->get() : collect();
        $classrooms = $teacher ? $teacher->classrooms()->with('department')->get() : collect();

        return view('teacher.dashboard', compact(
            'user',
            'teacher',
            'activeYear',
            'activeSemester',
            'subjects',
            'classrooms'
        ));
    }

    /**
     * Student Dashboard.
     */
    public function student(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $student = $user->studentProfile;

        if ($student) {
            $student->load(['classroom.department', 'classroom.academicYear']);
        }

        $activeYear = AcademicYear::active();
        $activeSemester = Semester::active();

        return view('student.dashboard', compact(
            'user',
            'student',
            'activeYear',
            'activeSemester'
        ));
    }
}
