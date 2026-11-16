<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CBT School
|--------------------------------------------------------------------------
*/

// Root Entry: redirect to dashboard if authenticated, else login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

// Theme Demo (Prototype Testing & Visual Verification)
Route::get('/theme-demo', function () {
    return view('adminlte-demo');
})->name('theme.demo');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Super Admin Route Group
    Route::prefix('admin')->name('admin.')->middleware('role:superadmin,admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/school-profile', [\App\Http\Controllers\Admin\SchoolProfileController::class, 'show'])->name('school.profile');
        Route::put('/school-profile', [\App\Http\Controllers\Admin\SchoolProfileController::class, 'update'])->name('school.profile.update');
        Route::get('/academic-years', [\App\Http\Controllers\Admin\AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::post('/academic-years', [\App\Http\Controllers\Admin\AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::put('/academic-years/{academicYear}', [\App\Http\Controllers\Admin\AcademicYearController::class, 'update'])->name('academic-years.update');
        Route::delete('/academic-years/{academicYear}', [\App\Http\Controllers\Admin\AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
        Route::post('/academic-years/{academicYear}/activate', [\App\Http\Controllers\Admin\AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::post('/semesters/{semester}/activate', [\App\Http\Controllers\Admin\AcademicYearController::class, 'activateSemester'])->name('semesters.activate');
        // Departments (Jurusan)
        Route::get('/departments', [\App\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [\App\Http\Controllers\Admin\DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Classes (Kelas)
        Route::get('/classes', [\App\Http\Controllers\Admin\ClassroomController::class, 'index'])->name('classes.index');
        Route::post('/classes', [\App\Http\Controllers\Admin\ClassroomController::class, 'store'])->name('classes.store');
        Route::put('/classes/{classroom}', [\App\Http\Controllers\Admin\ClassroomController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{classroom}', [\App\Http\Controllers\Admin\ClassroomController::class, 'destroy'])->name('classes.destroy');

        // Subjects (Mata Pelajaran)
        Route::get('/subjects', [\App\Http\Controllers\Admin\SubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects', [\App\Http\Controllers\Admin\SubjectController::class, 'store'])->name('subjects.store');
        Route::put('/subjects/{subject}', [\App\Http\Controllers\Admin\SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [\App\Http\Controllers\Admin\SubjectController::class, 'destroy'])->name('subjects.destroy');
        // Teachers (Guru)
        Route::get('/teachers', [\App\Http\Controllers\Admin\TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/create', [\App\Http\Controllers\Admin\TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [\App\Http\Controllers\Admin\TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [\App\Http\Controllers\Admin\TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [\App\Http\Controllers\Admin\TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [\App\Http\Controllers\Admin\TeacherController::class, 'destroy'])->name('teachers.destroy');
        // Students (Siswa)
        Route::get('/students', [\App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [\App\Http\Controllers\Admin\StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [\App\Http\Controllers\Admin\StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [\App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/exams', function () {
            return redirect()->route('teacher.exams.index');
        })->name('exams.index');
        // Exam Monitoring
        Route::get('/monitoring', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/{exam}', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'show'])->name('monitoring.show');
        Route::get('/monitoring/{exam}/live-data', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'liveData'])->name('monitoring.live-data');
        Route::get('/monitoring/{exam}/attempts/{attempt}', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'attemptDetail'])->name('monitoring.attempts.detail');
        Route::post('/monitoring/{exam}/attempts/{attempt}/force-finish', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'forceFinish'])->name('monitoring.attempts.force-finish');
        Route::post('/monitoring/{exam}/participants/{participant}/retake', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'authorizeRetake'])->name('monitoring.participants.retake');
        Route::get('/monitoring/{exam}/audit-logs', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'auditLogs'])->name('monitoring.audit-logs');
        // Exam Results & Scoring (P7-T04)
        Route::get('/results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'index'])->name('results.index');
        Route::get('/results/{exam}', [\App\Http\Controllers\Teacher\ExamResultController::class, 'show'])->name('results.show');
        Route::get('/results/{exam}/attempts/{attempt}', [\App\Http\Controllers\Teacher\ExamResultController::class, 'attemptDetail'])->name('results.attempts.detail');
        Route::post('/exams/{exam}/publish-results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'publishResults'])->name('exams.publish-results');
        Route::post('/exams/{exam}/unpublish-results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'unpublishResults'])->name('exams.unpublish-results');
        // Exam Reports & Analytics (P8-T01)
        Route::get('/reports', [\App\Http\Controllers\Teacher\ExamReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/exams/{exam}', [\App\Http\Controllers\Teacher\ExamReportController::class, 'examReport'])->name('reports.exam');
        Route::get('/reports/classes/{classroom}', [\App\Http\Controllers\Teacher\ExamReportController::class, 'classReport'])->name('reports.class');
    });

    // Teacher Route Group
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher,superadmin,admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'teacher'])->name('dashboard');

        // Question Banks (Bank Soal)
        Route::get('/question-banks', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'index'])->name('question-banks.index');
        Route::get('/question-banks/create', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'create'])->name('question-banks.create');
        Route::post('/question-banks', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'store'])->name('question-banks.store');
        Route::get('/question-banks/{question_bank}', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'show'])->name('question-banks.show');
        Route::get('/question-banks/{question_bank}/edit', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'edit'])->name('question-banks.edit');
        Route::put('/question-banks/{question_bank}', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'update'])->name('question-banks.update');
        Route::delete('/question-banks/{question_bank}', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'destroy'])->name('question-banks.destroy');

        // Questions within a Bank
        Route::get('/question-banks/{question_bank}/questions/create', [\App\Http\Controllers\Teacher\QuestionController::class, 'create'])->name('questions.create');
        Route::post('/question-banks/{question_bank}/questions', [\App\Http\Controllers\Teacher\QuestionController::class, 'store'])->name('questions.store');
        Route::get('/question-banks/{question_bank}/questions/{question}/edit', [\App\Http\Controllers\Teacher\QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/question-banks/{question_bank}/questions/{question}', [\App\Http\Controllers\Teacher\QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/question-banks/{question_bank}/questions/{question}', [\App\Http\Controllers\Teacher\QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::post('/question-banks/{question_bank}/questions/{question}/duplicate', [\App\Http\Controllers\Teacher\QuestionController::class, 'duplicate'])->name('questions.duplicate');

        // Exams Management (Jadwal Ujian)
        Route::resource('exams', \App\Http\Controllers\Teacher\ExamController::class);

        // Exam Questions Management (Pemilihan & Snapshot Soal)
        Route::get('/exams/{exam}/questions', [\App\Http\Controllers\Teacher\ExamQuestionController::class, 'index'])->name('exams.questions.index');
        Route::get('/exams/{exam}/questions/create', [\App\Http\Controllers\Teacher\ExamQuestionController::class, 'create'])->name('exams.questions.create');
        Route::post('/exams/{exam}/questions', [\App\Http\Controllers\Teacher\ExamQuestionController::class, 'store'])->name('exams.questions.store');
        Route::delete('/exams/{exam}/questions/{question}', [\App\Http\Controllers\Teacher\ExamQuestionController::class, 'destroy'])->name('exams.questions.destroy');

        // Exam Participants Management (Penetapan Peserta Ujian)
        Route::get('/exams/{exam}/participants', [\App\Http\Controllers\Teacher\ExamParticipantController::class, 'index'])->name('exams.participants.index');
        Route::get('/exams/{exam}/participants/create', [\App\Http\Controllers\Teacher\ExamParticipantController::class, 'create'])->name('exams.participants.create');
        Route::post('/exams/{exam}/participants', [\App\Http\Controllers\Teacher\ExamParticipantController::class, 'store'])->name('exams.participants.store');
        Route::delete('/exams/{exam}/participants/{participant}', [\App\Http\Controllers\Teacher\ExamParticipantController::class, 'destroy'])->name('exams.participants.destroy');
        Route::delete('/exams/{exam}/participants', [\App\Http\Controllers\Teacher\ExamParticipantController::class, 'destroyAll'])->name('exams.participants.destroy-all');

        // Exam Lifecycle & Preview (P5-T04)
        Route::post('/exams/{exam}/publish', [\App\Http\Controllers\Teacher\ExamController::class, 'publish'])->name('exams.publish');
        Route::post('/exams/{exam}/unpublish', [\App\Http\Controllers\Teacher\ExamController::class, 'unpublish'])->name('exams.unpublish');
        Route::post('/exams/{exam}/cancel', [\App\Http\Controllers\Teacher\ExamController::class, 'cancel'])->name('exams.cancel');
        Route::get('/exams/{exam}/preview', [\App\Http\Controllers\Teacher\ExamController::class, 'preview'])->name('exams.preview');

        // Exam Monitoring
        Route::get('/monitoring', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/{exam}', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'show'])->name('monitoring.show');
        Route::get('/monitoring/{exam}/live-data', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'liveData'])->name('monitoring.live-data');
        Route::get('/monitoring/{exam}/attempts/{attempt}', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'attemptDetail'])->name('monitoring.attempts.detail');
        Route::post('/monitoring/{exam}/attempts/{attempt}/force-finish', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'forceFinish'])->name('monitoring.attempts.force-finish');
        Route::post('/monitoring/{exam}/participants/{participant}/retake', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'authorizeRetake'])->name('monitoring.participants.retake');
        Route::get('/monitoring/{exam}/audit-logs', [\App\Http\Controllers\Teacher\ExamMonitoringController::class, 'auditLogs'])->name('monitoring.audit-logs');
        // Exam Results & Scoring (P7-T04)
        Route::get('/results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'index'])->name('results.index');
        Route::get('/results/{exam}', [\App\Http\Controllers\Teacher\ExamResultController::class, 'show'])->name('results.show');
        Route::get('/results/{exam}/attempts/{attempt}', [\App\Http\Controllers\Teacher\ExamResultController::class, 'attemptDetail'])->name('results.attempts.detail');
        Route::post('/exams/{exam}/publish-results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'publishResults'])->name('exams.publish-results');
        Route::post('/exams/{exam}/unpublish-results', [\App\Http\Controllers\Teacher\ExamResultController::class, 'unpublishResults'])->name('exams.unpublish-results');
        // Exam Reports & Analytics (P8-T01)
        Route::get('/reports', [\App\Http\Controllers\Teacher\ExamReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/exams/{exam}', [\App\Http\Controllers\Teacher\ExamReportController::class, 'examReport'])->name('reports.exam');
        Route::get('/reports/classes/{classroom}', [\App\Http\Controllers\Teacher\ExamReportController::class, 'classReport'])->name('reports.class');
    });

    // Student Route Group
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
        Route::get('/exams', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');
        Route::post('/exams/{exam}/start', [\App\Http\Controllers\Student\ExamAttemptController::class, 'start'])->name('exams.start');
        Route::get('/attempts/{attempt}', [\App\Http\Controllers\Student\ExamAttemptController::class, 'show'])->name('attempts.show');
        Route::get('/attempts/{attempt}/sync-time', [\App\Http\Controllers\Student\ExamAttemptController::class, 'syncTime'])->name('attempts.sync-time');
        Route::post('/attempts/{attempt}/finalize-expired', [\App\Http\Controllers\Student\ExamAttemptController::class, 'finalizeExpired'])->name('attempts.finalize-expired');
        Route::post('/attempts/{attempt}/save-answer', [\App\Http\Controllers\Student\ExamAttemptController::class, 'saveAnswer'])->name('attempts.save-answer');
        Route::post('/attempts/{attempt}/finish', [\App\Http\Controllers\Student\ExamAttemptController::class, 'finish'])->name('attempts.finish');
        // Exam Results (P7-T04)
        Route::get('/results', [\App\Http\Controllers\Student\ExamResultController::class, 'index'])->name('results.index');
        Route::get('/results/{exam}', [\App\Http\Controllers\Student\ExamResultController::class, 'show'])->name('results.show');
    });
});

// Media storage fallback (serves files from storage/app/public safely on LAN / Windows without symlink dependency)
Route::get('/storage/{path}', function (string $path) {
    if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.local');

// Error Preview Routes (For testing error page layouts)
Route::get('/error/403', function () { abort(403); })->name('error.403');
Route::get('/error/404', function () { abort(404); })->name('error.404');
Route::get('/error/500', function () { abort(500); })->name('error.500');
