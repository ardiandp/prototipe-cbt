<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject_id');
        $classroomId = $request->input('classroom_id');
        $status = $request->input('status');

        $query = Teacher::with(['user', 'subjects', 'classrooms'])->latest();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($subjectId)) {
            $query->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            });
        }

        if (! empty($classroomId)) {
            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $teachers = $query->paginate(10)->withQueryString();

        $subjects = Subject::active()->orderBy('name')->get();
        $classrooms = Classroom::active()->orderBy('name')->get();

        return view('admin.teachers.index', compact(
            'teachers',
            'subjects',
            'classrooms',
            'search',
            'subjectId',
            'classroomId',
            'status'
        ));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create(): View
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $classrooms = Classroom::active()->orderBy('name')->get();

        return view('admin.teachers.create', compact('subjects', 'classrooms'));
    }

    /**
     * Store a newly created teacher and user account.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active', true);

        DB::transaction(function () use ($data, $isActive) {
            // 1. Create User account for Teacher
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::TEACHER,
                'is_active' => $isActive,
            ]);

            // 2. Create Teacher Profile
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $isActive,
            ]);

            // 3. Sync Assignments
            if (! empty($data['subject_ids'])) {
                $teacher->subjects()->sync($data['subject_ids']);
            }

            if (! empty($data['classroom_ids'])) {
                $teacher->classrooms()->sync($data['classroom_ids']);
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru dan akun login berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load(['user', 'subjects', 'classrooms']);
        $subjects = Subject::active()->orderBy('name')->get();
        $classrooms = Classroom::active()->orderBy('name')->get();

        return view('admin.teachers.edit', compact('teacher', 'subjects', 'classrooms'));
    }

    /**
     * Update the specified teacher.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($data, $isActive, $teacher) {
            // 1. Update User Credentials
            $userUpdate = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'is_active' => $isActive,
            ];

            if (! empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }

            $teacher->user()->update($userUpdate);

            // 2. Update Teacher Profile
            $teacher->update([
                'nip' => $data['nip'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $isActive,
            ]);

            // 3. Sync Assignments
            $teacher->subjects()->sync($data['subject_ids'] ?? []);
            $teacher->classrooms()->sync($data['classroom_ids'] ?? []);
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru dan akun login berhasil diperbarui.');
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->subjects()->detach();
            $teacher->classrooms()->detach();
            $teacher->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data guru dan akun terkait berhasil dihapus.');
    }
}
