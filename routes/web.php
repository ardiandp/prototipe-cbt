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
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/school-profile', function () { return view('adminlte-demo'); })->name('school.profile');
        Route::get('/academic-years', function () { return view('adminlte-demo'); })->name('academic-years.index');
        Route::get('/classes', function () { return view('adminlte-demo'); })->name('classes.index');
        Route::get('/subjects', function () { return view('adminlte-demo'); })->name('subjects.index');
        Route::get('/teachers', function () { return view('adminlte-demo'); })->name('teachers.index');
        Route::get('/students', function () { return view('adminlte-demo'); })->name('students.index');
        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/monitoring', function () { return view('adminlte-demo'); })->name('monitoring.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
    });

    // Teacher Route Group
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/question-banks', function () { return view('adminlte-demo'); })->name('question-banks.index');
        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/monitoring', function () { return view('adminlte-demo'); })->name('monitoring.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
    });

    // Student Route Group
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/exams', function () { return view('adminlte-demo'); })->name('exams.index');
        Route::get('/results', function () { return view('adminlte-demo'); })->name('results.index');
    });
});

// Error Preview Routes (For testing error page layouts)
Route::get('/error/403', function () { abort(403); })->name('error.403');
Route::get('/error/404', function () { abort(404); })->name('error.404');
Route::get('/error/500', function () { abort(500); })->name('error.500');
