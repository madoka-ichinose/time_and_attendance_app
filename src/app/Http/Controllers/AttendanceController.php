<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function showStartScreen()
    {
        return view('attendance.start');
    }

    public function start(Request $request)
    {
        // 出勤処理ロジック（DB保存など）をここに
        return view('attendance.working');
    }

    public function end(Request $request)
    {
        // 退勤処理（未実装）
        return redirect('/');
    }

    public function break(Request $request)
    {
        // 休憩処理（未実装）
        return redirect('/');
    }
}
