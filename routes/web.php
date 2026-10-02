<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\AdminAboutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGalleryController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminMemberController;
use App\Http\Controllers\Admin\AdminQuoteController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tentang-kami', AboutController::class)->name('about');
Route::get('/anggota', [MemberController::class, 'index'])->name('members');
Route::get('/anggota/{anggota}', [MemberController::class, 'show'])->name('members.show');
Route::get('/guru', [TeacherController::class, 'index'])->name('teachers');
Route::get('/kenangan', [GalleryController::class, 'index'])->name('memories');

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store'])->middleware('throttle:5,1')->name('admin.login.store');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/beranda', [AdminHomeController::class, 'edit'])->name('home.edit');
    Route::put('/beranda', [AdminHomeController::class, 'update'])->name('home.update');
    Route::get('/tentang-kami', [AdminAboutController::class, 'edit'])->name('about.edit');
    Route::put('/tentang-kami', [AdminAboutController::class, 'update'])->name('about.update');
    Route::resource('/anggota', AdminMemberController::class)
        ->except('show')
        ->parameters(['anggota' => 'anggota'])
        ->names('members');
    Route::resource('/guru', AdminTeacherController::class)->except('show')->names('teachers');
    Route::resource('/kenangan', AdminGalleryController::class)->except('show')->names('memories');
    Route::get('/kata-kata', [AdminQuoteController::class, 'index'])->name('quotes.index');
    Route::post('/kata-kata', [AdminQuoteController::class, 'store'])->name('quotes.store');
    Route::put('/kata-kata/{quote}', [AdminQuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/kata-kata/{quote}', [AdminQuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::get('/pengaturan', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [AdminSettingController::class, 'update'])->name('settings.update');
});
