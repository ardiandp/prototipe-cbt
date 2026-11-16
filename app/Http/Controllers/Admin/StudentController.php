<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $classroomId = $request->input('classroom_id');
        $gender = $request->input('gender');
        $status = $request->input('status');

        $query = Student::with(['user', 'classroom.department'])->latest();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($classroomId)) {
            $query->where('classroom_id', $classroomId);
        }

        if (! empty($gender)) {
            $query->where('gender', $gender);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $students = $query->paginate(10)->withQueryString();

        $classrooms = Classroom::active()->with('department')->orderBy('name')->get();

        return view('admin.students.index', compact(
            'students',
            'classrooms',
            'search',
            'classroomId',
            'gender',
            'status'
        ));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): View
    {
        $classrooms = Classroom::active()->with('department')->orderBy('name')->get();

        return view('admin.students.create', compact('classrooms'));
    }

    /**
     * Store a newly created student and user account.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active', true);

        DB::transaction(function () use ($data, $isActive) {
            // 1. Create User account for Student
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::STUDENT,
                'is_active' => $isActive,
            ]);

            // 2. Create Student Profile
            Student::create([
                'user_id' => $user->id,
                'classroom_id' => $data['classroom_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa dan akun login berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): View
    {
        $student->load(['user', 'classroom']);
        $classrooms = Classroom::active()->with('department')->orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'classrooms'));
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($data, $isActive, $student) {
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

            $student->user()->update($userUpdate);

            // 2. Update Student Profile
            $student->update([
                'classroom_id' => $data['classroom_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa dan akun login berhasil diperbarui.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student): RedirectResponse
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa dan akun terkait berhasil dihapus.');
    }
}

