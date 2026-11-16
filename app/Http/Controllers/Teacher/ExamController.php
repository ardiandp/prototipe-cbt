<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreExamRequest;
use App\Http\Requests\Teacher\UpdateExamRequest;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExamController extends Controller
{
    /**
     * Display a listing of scheduled exams.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Exam::with(['subject', 'teacher', 'academicYear', 'semester']);

        // Teacher only sees exams they created
        if ($user->isTeacher() && $user->teacherProfile) {
            $query->where('teacher_id', $user->teacherProfile->id);
        }

        // Search by keyword (title or code)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by subject
        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            if (in_array($status, ['draft', 'published', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        $exams = $query->orderBy('start_at', 'desc')->paginate(10)->withQueryString();

        // Subjects for filter dropdown
        $subjectsQuery = Subject::active();
        if ($user->isTeacher() && $user->teacherProfile) {
            $subjectsQuery->whereIn('id', $user->teacherProfile->subjects()->pluck('subjects.id'));
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        return view('teacher.exams.index', compact('exams', 'subjects'));
    }

    /**
     * Show the form for creating a new exam schedule.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Available subjects for the teacher / admin
        $subjectsQuery = Subject::active();
        if ($user->isTeacher() && $user->teacherProfile) {
            $teacherSubjectIds = $user->teacherProfile->subjects()->pluck('subjects.id');
            if ($teacherSubjectIds->isNotEmpty()) {
                $subjectsQuery->whereIn('id', $teacherSubjectIds);
            }
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        // Default start time: tomorrow at 08:00
        $defaultStart = Carbon::tomorrow()->setHour(8)->setMinute(0);
        $defaultEnd = (clone $defaultStart)->addHours(4);

        return view('teacher.exams.create', compact('subjects', 'activeYear', 'activeSemester', 'defaultStart', 'defaultEnd'));
    }

    /**
     * Store a newly created exam schedule.
     */
    public function store(StoreExamRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        // Auto-generate exam code if not provided
        if (empty($data['code'])) {
            $data['code'] = 'UJN-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        }

        $teacherId = null;
        if ($user->isTeacher() && $user->teacherProfile) {
            $teacherId = $user->teacherProfile->id;
        }

        $exam = Exam::create([
            'subject_id' => $data['subject_id'],
            'teacher_id' => $teacherId,
            'academic_year_id' => $activeYear?->id,
            'semester_id' => $activeSemester?->id,
            'code' => $data['code'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'duration' => $data['duration'],
            'passing_grade' => $data['passing_grade'],
            'random_question' => $request->boolean('random_question'),
            'random_answer' => $request->boolean('random_answer'),
            'token' => ! empty($data['token']) ? strtoupper($data['token']) : null,
            'status' => $data['status'],
            'is_results_published' => false,
        ]);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Jadwal ujian berhasil dibuat.');
    }

    /**
     * Display the specified exam details.
     */
    public function show(Request $request, Exam $exam): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $exam->teacher_id !== $user->teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat jadwal ujian ini.');
        }

        $exam->load(['subject', 'teacher.user', 'academicYear', 'semester']);

        return view('teacher.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Request $request, Exam $exam): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $exam->teacher_id !== $user->teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit jadwal ujian ini.');
        }

        $subjectsQuery = Subject::active();
        if ($user->isTeacher() && $user->teacherProfile) {
            $teacherSubjectIds = $user->teacherProfile->subjects()->pluck('subjects.id');
            if ($teacherSubjectIds->isNotEmpty()) {
                $subjectsQuery->whereIn('id', $teacherSubjectIds);
            }
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        return view('teacher.exams.edit', compact('exam', 'subjects'));
    }

    /**
     * Update the specified exam.
     */
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->validated();

        $exam->update([
            'code' => $data['code'] ?? $exam->code,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'subject_id' => $data['subject_id'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'duration' => $data['duration'],
            'passing_grade' => $data['passing_grade'],
            'random_question' => $request->boolean('random_question'),
            'random_answer' => $request->boolean('random_answer'),
            'token' => ! empty($data['token']) ? strtoupper($data['token']) : null,
            'status' => $data['status'],
        ]);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $exam->teacher_id !== $user->teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus jadwal ujian ini.');
        }

        $exam->delete();

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Jadwal ujian berhasil dihapus.');
    }
}
