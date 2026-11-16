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
        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/monitoring', function () { return view('adminlte-demo'); })->name('monitoring.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
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

        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/monitoring', function () { return view('adminlte-demo'); })->name('monitoring.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
    });

    // Student Route Group
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
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
