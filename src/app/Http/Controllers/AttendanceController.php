<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function showStartScreen()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // 今日の出勤記録を取得
        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->first();

        if ($attendance) {
            if ($attendance->clock_out) {
                // 退勤済 → end 画面を表示
                return view('attendance.end');
            } elseif ($attendance->clock_in) {
                // 出勤済・退勤前 → working 画面を表示
                return view('attendance.working');
            }
        }

        // 出勤前なら出勤ボタンのある画面へ
        return view('attendance.start');
    }

    public function start(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        // 出勤済みか確認（1日1回制限）
        $alreadyStarted = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->exists();

        if ($alreadyStarted) {
            return redirect()->route('attendance.working')->with('error', '本日はすでに出勤済みです。');
        }

        $attendance = Attendance::create([
        'user_id' => $user->id,
        'work_date' => $today,
        'clock_in' => Carbon::now(),
    ]);

        return redirect()->route('attendance.working');
    }

    public function break(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
    
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();
    
        if ($attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.break.screen');
    }

    public function showBreakScreen()
    {
        return view('attendance.break');
    }

    public function breakReturn(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->first();

        if ($attendance) {
            $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if ($break) {
            $break->end_time = Carbon::now();
            $break->save();
        }
    }

        return redirect()->route('attendance.working');
    }

    public function showWorkingScreen()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->first();

        if ($attendance && $attendance->clock_out) {
        // 退勤済み → end画面へリダイレクト
        return redirect()->route('attendance.end.screen');
        }

        return view('attendance.working');
    }

    public function end(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
    
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();
    
        if ($attendance) {
            $attendance->clock_out = Carbon::now();
            $diff = $attendance->clock_out->diffInMinutes($attendance->clock_in);
    
            $breaks = $attendance->breaks;
            $breakMinutes = $breaks->sum(function ($break) {
                return $break->end_time && $break->start_time
                    ? Carbon::parse($break->end_time)->diffInMinutes($break->start_time)
                    : 0;
            });
    
            $attendance->total_work_minutes = $diff - $breakMinutes;
            $attendance->save();
        }

        return redirect()->route('attendance.end.screen');
    }

    public function showEndScreen()
    {
    return view('attendance.end');
    }

}
