<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Models\BreakTime;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    $credentials['role'] = 'admin'; // 管理者のみ

    if (Auth::attempt($credentials)) {
        return redirect('/admin/attendance/list');
    }

    return back()->withErrors(['admin.login' => 'メールアドレスまたはパスワードが正しくありません。']);
}

public function index(Request $request)
{
    $date = $request->input('date') 
        ? Carbon::parse($request->input('date')) 
        : Carbon::today();

    $users = User::where('role', 'user')
        ->with(['attendances' => function ($query) use ($date) {
            $query->whereDate('work_date', $date->toDateString())->with('breaks');
        }])
        ->get();

    $attendances = $users->map(function ($user) use ($date) {
        $attendance = $user->attendances->first();

        if ($attendance) {
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '--:--';
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '--:--';

            // 休憩合計計算
            $breakMinutes = $attendance->breaks->sum(function ($break) {
                return $break->end_time && $break->start_time
                    ? Carbon::parse($break->end_time)->diffInMinutes($break->start_time)
                    : 0;
            });

            $breakTime = $this->formatMinutes($breakMinutes);

            // 勤務合計（total_work_minutesが既にあるならそれを使う）
            $workTime = $attendance->total_work_minutes !== null
                ? $this->formatMinutes($attendance->total_work_minutes)
                : '--:--';
        } else {
            $clockIn = $clockOut = $breakTime = $workTime = '--:--';
        }

        return [
            'user_name' => $user->name,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_time' => $breakTime,
            'work_time' => $workTime,
        ];
    });

    return view('admin.attendance.list', [
        'date' => $date,
        'attendances' => $attendances
    ]);
}

// 勤務・休憩時間の整形メソッド（コピペOK）
private function formatMinutes($minutes)
{
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $mins);
}
}
