<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tentang-kami', AboutController::class)->name('about');
Route::get('/anggota', [MemberController::class, 'index'])->name('members');
Route::get('/guru', [TeacherController::class, 'index'])->name('teachers');
Route::get('/kenangan', [GalleryController::class, 'index'])->name('memories');
