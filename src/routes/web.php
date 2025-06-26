<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Admin\StaffController;

// 一般ユーザー用ログイン画面
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware('auth','verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showStartScreen'])->name('attendance.show');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::get('/attendance/break', [AttendanceController::class, 'showBreakScreen'])->name('attendance.break.screen');
    Route::post('/attendance/break', [AttendanceController::class, 'break'])->name('attendance.break');
    Route::post('/attendance/break/return', [AttendanceController::class, 'breakReturn'])->name('attendance.break.return');
    Route::get('/attendance/working', [AttendanceController::class, 'showWorkingScreen'])->name('attendance.working');
    Route::get('/attendance/end-screen', [AttendanceController::class, 'showEndScreen'])->name('attendance.end.screen');
    Route::get('/attendance/list{year?}/{month?}', [AttendanceController::class,'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/request/{id}', [AttendanceController::class, 'submitRequest'])->name('attendance.request.submit');
    Route::get('/request/list', [RequestController::class, 'index'])->name('request.list');
    Route::get('/attendance/{date}/edit', [AttendanceController::class, 'createOrEdit'])->name('attendance.createOrEdit');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');


});

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.list');
});

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/admin/staff_list', [StaffController::class, 'index'])->name('admin.staff.index');
});

Route::middleware(['auth', 'can:isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance/{user}/monthly', [AdminController::class, 'monthly'])->name('attendance.monthly');
    Route::get('/attendance/{attendance}/detail', [AdminController::class, 'detail'])->name('attendance.detail');
    Route::put('/attendance/{attendance}/update', [AdminController::class, 'updateAttendance'])->name('attendance.update');
    Route::get('/requests', [AdminController::class, 'requestList'])->name('requests.index');

    Route::post('/admin/request/{id}/approve', [AdminController::class, 'approve'])->name('admin.request.approve');

});

Route::get('/admin/attendance/csv', [AdminController::class, 'exportCsv'])->name('admin.attendance.csv');
