<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Requests\Admin\UpdateClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    /**
     * Display a listing of classrooms.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $level = $request->input('level');
        $departmentId = $request->input('department_id');
        $academicYearId = $request->input('academic_year_id');
        $status = $request->input('status');

        $query = Classroom::with(['department', 'academicYear'])->latest();

        if (! empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        if (! empty($level)) {
            $query->where('level', $level);
        }

        if (! empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (! empty($academicYearId)) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $classrooms = $query->paginate(10)->withQueryString();

        $departments = Department::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('name')->get();

        return view('admin.classes.index', compact(
            'classrooms',
            'departments',
            'academicYears',
            'search',
            'level',
            'departmentId',
            'academicYearId',
            'status'
        ));
    }

    /**
     * Store a newly created classroom.
     */
    public function store(StoreClassroomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        // If no academic year specified, default to active academic year if available
        if (empty($data['academic_year_id'])) {
            $activeYear = AcademicYear::active();
            if ($activeYear) {
                $data['academic_year_id'] = $activeYear->id;
            }
        }

        Classroom::create($data);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Update the specified classroom.
     */
    public function update(UpdateClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $classroom->update($data);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified classroom.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}

