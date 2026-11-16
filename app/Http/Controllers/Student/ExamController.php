<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamParticipant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    /**
     * Display a listing of exams assigned to the authenticated student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student) {
            $ongoingExams = collect();
            $upcomingExams = collect();
            $pastExams = collect();

            return view('student.exams.index', compact('ongoingExams', 'upcomingExams', 'pastExams'));
        }

        // Get IDs of exams where student is assigned
        $assignedExamIds = ExamParticipant::where('student_id', $student->id)
            ->pluck('exam_id');

        // Fetch published exams for this student
        $exams = Exam::whereIn('id', $assignedExamIds)
            ->published()
            ->with(['subject', 'teacher.user'])
            ->orderBy('start_at')
            ->get();

        // Categorize into ongoing, upcoming, and past
        $ongoingExams = $exams->filter(fn (Exam $e) => $e->isOpen());
        $upcomingExams = $exams->filter(fn (Exam $e) => $e->isUpcoming());
        $pastExams = $exams->filter(fn (Exam $e) => $e->isPast());

        return view('student.exams.index', compact('ongoingExams', 'upcomingExams', 'pastExams'));
    }

    /**
     * Display the exam preparation / rules screen for the authenticated student.
     */
    public function show(Request $request, Exam $exam): View
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student) {
            abort(403, 'Profil peserta didik tidak ditemukan.');
        }

        // Participant check
        $participant = ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $participant) {
            abort(403, 'Anda tidak terdaftar sebagai peserta dalam jadwal ujian ini.');
        }

        // Only published exams are accessible to students
        if (! $exam->isPublished()) {
            abort(404, 'Jadwal ujian belum dipublikasikan atau telah dinonaktifkan.');
        }

        $exam->load(['subject', 'teacher.user', 'academicYear', 'semester']);

        return view('student.exams.show', compact('exam', 'participant'));
    }
}
