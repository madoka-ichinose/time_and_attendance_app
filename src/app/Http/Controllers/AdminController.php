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

        // 勤怠データがない場合は作成
        if (!$attendance) {
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date->toDateString()],
            ['clock_in' => null, 'clock_out' => null, 'note' => null, 'total_work_minutes' => 0]
        );
        $attendance->load('breaks');
    }

        $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '--:--';
        $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '--:--';

        $breakMinutes = $attendance->breaks->sum(function ($break) {
        return $break->end_time && $break->start_time
            ? Carbon::parse($break->end_time)->diffInMinutes($break->start_time)
            : 0;
    });

        $breakTime = $this->formatMinutes($breakMinutes);
        $workTime = $attendance->total_work_minutes !== null
        ? $this->formatMinutes($attendance->total_work_minutes)
        : '--:--';

        return [
        'id' => $attendance->id, // ← 常にあるようになる
        'user_id' => $user->id,
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

    private function formatBreakTime($breaks)
    {
        $total = $breaks->sum(function ($break) {
        return $break->start_time && $break->end_time
            ? \Carbon\Carbon::parse($break->end_time)->diffInMinutes(\Carbon\Carbon::parse($break->start_time))
            : 0;
        });

        return $this->formatMinutes($total);
    }

    // 勤務・休憩時間の整形メソッド（コピペOK）
    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function monthly(User $user, Request $request)
    {
        $currentMonth = $request->month ? Carbon::parse($request->month) : now()->startOfMonth();
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // 勤怠情報（該当月）を取得して日付でキー付け
        $attendanceRaw = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
        ->with('breaks')
        ->get()
        ->keyBy(function ($item) {
            return Carbon::parse($item->work_date)->format('Y-m-d');
        });

        // 表示用データに整形
        $attendances = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
        $record = $attendanceRaw->get($date->format('Y-m-d'));

        $attendances[] = [
            'id' => $record->id ?? null,
            'date' => $date->format('Y-m-d'),
            'display_date' => $date->locale('ja')->isoFormat('MM/DD（dd）'),
            'clock_in' => $record && $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '--:--',
            'clock_out' => $record && $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '--:--',
            'break_time' => $record ? $this->formatBreakTime($record->breaks) : '--:--',
            'work_time' => $record && $record->total_work_minutes ? $this->formatMinutes($record->total_work_minutes) : '--:--',
        ];
    }

        return view('admin.attendance_monthly', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }


    public function detail(Attendance $attendance)
    {
        $user = $attendance->user; // リレーションから取得
        return view('admin.attendance_detail', compact('attendance', 'user'));
    }

    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
        'clock_in' => 'nullable|date_format:H:i',
        'clock_out' => 'nullable|date_format:H:i|after_or_equal:clock_in',
        'note' => 'nullable|string|max:1000',
        'breaks.*.start_time' => 'nullable|date_format:H:i',
        'breaks.*.end_time' => 'nullable|date_format:H:i|after_or_equal:breaks.*.start_time',
    ]);

    // 出退勤を更新
        $attendance->update([
        'clock_in' => $attendance->work_date . ' ' . $request->input('clock_in'),
        'clock_out' => $attendance->work_date . ' ' . $request->input('clock_out'),
        'note' => $request->input('note'),
    ]);

    // 既存の休憩を全削除してから再登録（差分更新が不要な場合）
        $attendance->breaks()->delete();

        foreach ($request->input('breaks', []) as $break) {
        if (!empty($break['start_time']) && !empty($break['end_time'])) {
            $attendance->breaks()->create([
                'start_time' => $attendance->work_date . ' ' . $break['start_time'],
                'end_time' => $attendance->work_date . ' ' . $break['end_time'],
            ]);
        }
    }

    // 勤務時間の再計算（オプション）
        $this->recalculateWorkMinutes($attendance);

        return redirect()->route('admin.attendance.edit', ['attendance' => $attendance->id])
        ->with('success', '勤怠情報を更新しました。');
    }

    private function recalculateWorkMinutes(Attendance $attendance)
    {
        if (!$attendance->clock_in || !$attendance->clock_out) return;

        $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)
        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in));

        $breakMinutes = $attendance->breaks->sum(function ($break) {
        return \Carbon\Carbon::parse($break->end_time)->diffInMinutes($break->start_time);
    });

        $attendance->total_work_minutes = max(0, $workMinutes - $breakMinutes);
        $attendance->save();
    }

}
