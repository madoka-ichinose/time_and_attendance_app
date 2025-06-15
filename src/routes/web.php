<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\RequestController;

Route::middleware('auth','verified')->group(function () {
    Route::get('/', [AttendanceController::class, 'showStartScreen'])->name('attendance.show');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::get('/attendance/break', [AttendanceController::class, 'showBreakScreen'])->name('attendance.break.screen');
    Route::post('/attendance/break', [AttendanceController::class, 'break'])->name('attendance.break');
    Route::post('/attendance/break/return', [AttendanceController::class, 'breakReturn'])->name('attendance.break.return');
    Route::get('/attendance/working', [AttendanceController::class, 'showWorkingScreen'])->name('attendance.working');
    Route::get('/attendance/end-screen', [AttendanceController::class, 'showEndScreen'])->name('attendance.end.screen');
    Route::get('/attendance/list{year?}/{month?}', [AttendanceController::class,'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/request/{id}', [AttendanceController::class, 'submitRequest'])->name('attendance.request');
    Route::get('/request/list', [RequestController::class, 'index'])->name('request.list');

});

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
