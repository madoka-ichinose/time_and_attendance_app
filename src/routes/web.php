<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'showStartScreen'])->name('attendance.show');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/break', [AttendanceController::class, 'break'])->name('attendance.break');
});