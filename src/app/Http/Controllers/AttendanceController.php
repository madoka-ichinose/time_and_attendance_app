<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\RequestApplication;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;


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

    public function index($year = null, $month = null)
    {
    $user = auth()->user();
    $today = Carbon::today();

    // 現在の月を取得（パラメータがない場合は今日）
    $targetDate = Carbon::createFromDate($year ?? $today->year, $month ?? $today->month, 1);

    // 前月・翌月の計算
    $prevMonth = $targetDate->copy()->subMonth();
    $nextMonth = $targetDate->copy()->addMonth();

    // 対象月の全日付を取得
    $startOfMonth = $targetDate->copy()->startOfMonth();
    $endOfMonth = $targetDate->copy()->endOfMonth();

    // 対象月の勤怠記録を取得
    $attendancesRaw = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy(function ($item) {
            return Carbon::parse($item->work_date)->format('Y-m-d');
        });

    // カレンダーのように全日分ループ
    $attendances = [];
    for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
        $record = $attendancesRaw->get($date->format('Y-m-d'));
        
        $attendances[] = [
            'id' => $record->id ?? null,
            'display_date' => $date->locale('ja')->isoFormat('MM/DD（dd）'),
            'date' => $date->format('Y-m-d'),
            'clock_in' => isset($record) && $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '--:--',
            'clock_out' => isset($record) && $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '--:--',
            'break_time' => isset($record) ? $this->formatBreakTime($record->breaks) : '--:--',
            'work_time' => isset($record) && $record->total_work_minutes ? $this->formatMinutes($record->total_work_minutes) : '--:--',
        ];
        
    }

    return view('attendance.list', [
        'attendances' => $attendances,
        'currentMonth' => $targetDate->format('Y/m'),
        'prevMonthUrl' => route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]),
        'nextMonthUrl' => route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]),
    ]);
    }

// 勤務・休憩時間の整形メソッド
    private function formatMinutes($minutes)
    {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $mins);
    }

    private function formatBreakTime($breaks)
    {
    $total = $breaks->sum(function ($break) {
        return $break->end_time && $break->start_time
            ? Carbon::parse($break->end_time)->diffInMinutes($break->start_time)
            : 0;
    });
    return $this->formatMinutes($total);
    }

    public function detail($id)
    {
    $attendance = Attendance::with('breaks')->findOrFail($id);
    $user = Auth::user();

    return view('attendance.detail', compact('attendance', 'user'));
    }

    public function submitRequest(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

    // 時刻の更新
        if ($request->filled('clock_in')) {
        $attendance->clock_in = \Carbon\Carbon::parse($attendance->work_date . ' ' . $request->input('clock_in'));
    }

        if ($request->filled('clock_out')) {
        $attendance->clock_out = \Carbon\Carbon::parse($attendance->work_date . ' ' . $request->input('clock_out'));
    }

        $attendance->note = $request->input('note');
        $attendance->save();

    // 休憩時間更新
        if ($request->has('breaks')) {
        foreach ($request->input('breaks') as $breakId => $times) {
            $break = \App\Models\BreakTime::find($breakId);
            if ($break && $break->attendance_id == $attendance->id) {
                if (!empty($times['start'])) {
                    $break->start_time = \Carbon\Carbon::parse($attendance->work_date . ' ' . $times['start']);
                }
                if (!empty($times['end'])) {
                    $break->end_time = \Carbon\Carbon::parse($attendance->work_date . ' ' . $times['end']);
                }
                $break->save();
            }
        }
    }

    // 申請記録作成
    \App\Models\RequestApplication::create([
        'attendance_id' => $attendance->id,
        'user_id' => auth()->id(),
        'status' => '承認待ち',
        'reason' => $attendance->note,
        'applied_at' => Carbon::now(),
    ]);

    return redirect()->route('request.list');
}


}
