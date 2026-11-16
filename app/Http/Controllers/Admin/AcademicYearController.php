<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years and their semesters.
     */
    public function index(): View
    {
        $academicYears = AcademicYear::with('semesters')->orderByDesc('is_active')->orderByDesc('name')->get();
        $activeYear = AcademicYear::active();
        $activeSemester = Semester::active();

        return view('admin.academic-years.index', [
            'academicYears' => $academicYears,
            'activeYear' => $activeYear,
            'activeSemester' => $activeSemester,
        ]);
    }

    /**
     * Store a newly created academic year.
     */
    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isActive = (bool) ($data['is_active'] ?? false);

        DB::transaction(function () use ($data, $isActive) {
            $year = AcademicYear::create([
                'name' => $data['name'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_active' => false,
            ]);

            // Auto-create default semesters Ganjil & Genap
            $year->semesters()->create([
                'name' => 'Ganjil',
                'is_active' => false,
            ]);

            $year->semesters()->create([
                'name' => 'Genap',
                'is_active' => false,
            ]);

            if ($isActive) {
                $year->activate();
                $year->semesters()->first()?->activate();
            }
        });

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran baru dan semester terkait berhasil ditambahkan.');
    }

    /**
     * Update the specified academic year.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validated();

        $academicYear->update([
            'name' => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);

        if (! empty($data['is_active'])) {
            $academicYear->activate();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified academic year.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return redirect()->route('admin.academic-years.index')
                ->with('error', 'Tahun ajaran yang sedang aktif tidak dapat dihapus.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        // If no semester in this year is active, activate the first one
        if (! $academicYear->semesters()->where('is_active', true)->exists()) {
            $academicYear->semesters()->first()?->activate();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->name} berhasil diaktifkan sebagai periode aktif.");
    }

    /**
     * Activate the specified semester.
     */
    public function activateSemester(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Semester {$semester->name} ({$semester->academicYear->name}) berhasil diaktifkan.");
    }
}
