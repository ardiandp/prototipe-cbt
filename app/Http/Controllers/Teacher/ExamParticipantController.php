<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamParticipantController extends Controller
{
    /**
     * Display a listing of assigned participants for this exam.
     */
    public function index(Request $request, Exam $exam): View
    {
        $this->authorizeExamAccess($request, $exam);

        $query = $exam->participants()->with(['student.user', 'classroom']);

        // Filter by classroom
        if ($classroomId = $request->input('classroom_id')) {
            $query->where('classroom_id', $classroomId);
        }

        // Search by name or NIS
        if ($search = $request->input('search')) {
            $query->whereHas('student', function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $participants = $query->orderBy('classroom_id')->orderBy('id')->paginate(20)->withQueryString();

        // Classrooms that have participants in this exam
        $classroomIds = $exam->participants()->distinct()->pluck('classroom_id')->filter();
        $classrooms = Classroom::whereIn('id', $classroomIds)->orderBy('name')->get();

        return view('teacher.exams.participants.index', compact('exam', 'participants', 'classrooms'));
    }

    /**
     * Show the participant assignment page (by class or individual).
     */
    public function create(Request $request, Exam $exam): View
    {
        $this->authorizeExamAccess($request, $exam);

        $allClassrooms = Classroom::active()->withCount('students')->orderBy('level')->orderBy('name')->get();

        $alreadyAssignedIds = $exam->participants()->pluck('student_id')->toArray();

        // Individual student picker query
        $studentQuery = Student::active()->with(['user', 'classroom'])
            ->whereNotIn('id', $alreadyAssignedIds);

        if ($classroomId = $request->input('student_classroom_id')) {
            $studentQuery->where('classroom_id', $classroomId);
        }

        if ($search = $request->input('student_search')) {
            $studentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $availableStudents = $studentQuery->orderBy('classroom_id')->orderBy('name')->paginate(20)->withQueryString();

        return view('teacher.exams.participants.create', compact('exam', 'allClassrooms', 'availableStudents', 'alreadyAssignedIds'));
    }

    /**
     * Store newly assigned participants.
     */
    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExamAccess($request, $exam);

        $assignMode = $request->input('assign_mode', 'individual');

        if ($assignMode === 'classroom') {
            $validated = $request->validate([
                'classroom_ids' => ['required', 'array', 'min:1'],
                'classroom_ids.*' => ['required', 'exists:classrooms,id'],
            ], [
                'classroom_ids.required' => 'Silakan pilih minimal satu kelas rombongan belajar.',
            ]);

            $students = Student::active()
                ->whereIn('classroom_id', $validated['classroom_ids'])
                ->get();

            $addedCount = 0;
            DB::transaction(function () use ($exam, $students, &$addedCount) {
                foreach ($students as $student) {
                    $participant = ExamParticipant::firstOrCreate(
                        [
                            'exam_id' => $exam->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'classroom_id' => $student->classroom_id,
                            'status' => 'registered',
                        ]
                    );

                    if ($participant->wasRecentlyCreated) {
                        $addedCount++;
                    }
                }
            });

            return redirect()->route('teacher.exams.participants.index', $exam)
                ->with('success', "Sebanyak {$addedCount} peserta didik dari kelas terpilih berhasil ditambahkan.");
        }

        // Mode: Individual
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:students,id'],
        ], [
            'student_ids.required' => 'Silakan centang minimal satu peserta didik untuk ditugaskan.',
        ]);

        $students = Student::active()->whereIn('id', $validated['student_ids'])->get();

        $addedCount = 0;
        DB::transaction(function () use ($exam, $students, &$addedCount) {
            foreach ($students as $student) {
                $participant = ExamParticipant::firstOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'classroom_id' => $student->classroom_id,
                        'status' => 'registered',
                    ]
                );

                if ($participant->wasRecentlyCreated) {
                    $addedCount++;
                }
            }
        });

        return redirect()->route('teacher.exams.participants.index', $exam)
            ->with('success', "Sebanyak {$addedCount} peserta didik berhasil ditugaskan ke jadwal ujian.");
    }

    /**
     * Remove a participant from this exam.
     */
    public function destroy(Request $request, Exam $exam, ExamParticipant $participant): RedirectResponse
    {
        $this->authorizeExamAccess($request, $exam);

        if ($participant->exam_id !== $exam->id) {
            abort(404);
        }

        $participant->delete();

        return redirect()->route('teacher.exams.participants.index', $exam)
            ->with('success', 'Peserta berhasil dihapus dari jadwal ujian.');
    }

    /**
     * Clear all participants from this exam.
     */
    public function destroyAll(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExamAccess($request, $exam);

        $count = $exam->participants()->count();
        $exam->participants()->delete();

        return redirect()->route('teacher.exams.participants.index', $exam)
            ->with('success', "Seluruh {$count} peserta ujian berhasil dikosongkan.");
    }

    /**
     * Authorize that the current user has rights to access/manage this exam.
     */
    private function authorizeExamAccess(Request $request, Exam $exam): void
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isAnyAdmin()) {
            return;
        }

        if ($user->isTeacher() && $exam->teacher_id === $user->teacherProfile?->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak akses untuk mengelola peserta pada jadwal ujian ini.');
    }
}
