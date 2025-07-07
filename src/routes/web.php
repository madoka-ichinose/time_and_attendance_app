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

// ==========================
// 一般ユーザー認証関連（PG01, PG02）
// ==========================

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================
// メール認証
// ==========================

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

// ==========================
// 一般ユーザーの勤怠関連（PG03〜PG06）
// ==========================

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showStartScreen'])->name('attendance.show');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::get('/attendance/break', [AttendanceController::class, 'showBreakScreen'])->name('attendance.break.screen');
    Route::post('/attendance/break', [AttendanceController::class, 'break'])->name('attendance.break');
    Route::post('/attendance/break/return', [AttendanceController::class, 'breakReturn'])->name('attendance.break.return');

    Route::get('/attendance/working', [AttendanceController::class, 'showWorkingScreen'])->name('attendance.working');
    Route::get('/attendance/end-screen', [AttendanceController::class, 'showEndScreen'])->name('attendance.end.screen');

    Route::get('/attendance/list/{year?}/{month?}', [AttendanceController::class,'index'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/request/{id}', [AttendanceController::class, 'submitRequest'])->name('attendance.request.submit');

    Route::get('/attendance/{date}/edit', [AttendanceController::class, 'createOrEdit'])->name('attendance.createOrEdit');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // 修正申請一覧（PG06・PG12 共通パス）
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('request.list');
});

// ==========================
// 管理者ログイン（PG07）
// ==========================

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);

// ==========================
// 管理者：勤怠関連（PG08, PG09, PG11）
// ==========================

Route::middleware(['auth', 'can:isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/attendance/list', [AdminController::class, 'index'])->name('attendance.list'); // PG08
    Route::put('/attendance/{attendance}/update', [AdminController::class, 'updateAttendance'])->name('attendance.update');
    Route::get('/attendance/staff/{user}', [AdminController::class, 'monthly'])->name('attendance.monthly'); // PG11
    Route::get('/attendance/detail/{user_id}/{work_date}', [AdminController::class, 'detail'])->name('attendance.detail'); // PG09
    // CSV出力（非画面定義だけど実装済み）
    Route::get('/attendance/csv', [AdminController::class, 'exportCsv'])->name('attendance.csv');

    // 修正申請一覧・詳細・承認（PG12, PG13）
    Route::get('/stamp_correction_request/list', [AdminController::class, 'requestList'])->name('requests.index');
    Route::get('/requests/{request}/detail', [AdminController::class, 'requestDetail'])->name('requests.detail');
    Route::put('/stamp_correction_request/approve/{request}', [AdminController::class, 'approveRequest'])->name('requests.approve');
});

// ==========================
// 管理者：スタッフ一覧（PG10）
// ==========================

Route::middleware(['auth', 'can:isAdmin'])->get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');

