<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('adminlte-demo');
})->name('home');

Route::get('/theme-demo', function () {
    return view('adminlte-demo');
})->name('theme.demo');
