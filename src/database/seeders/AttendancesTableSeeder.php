<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            for ($i = 0; $i < 90; $i++) {
                $date = Carbon::today()->subDays($i);

                // 出勤時間（8:00〜9:30の間でランダム）
                $clockIn = Carbon::createFromTime(rand(8, 9), rand(0, 59));

                // 休憩時間：45〜75分（1〜3回に分割）
                $breakTotalMinutes = rand(45, 75);
                $breakCount = rand(1, 3);
                $breaks = [];
                $remainingBreak = $breakTotalMinutes;

                for ($j = 0; $j < $breakCount; $j++) {
                    $length = ($j === $breakCount - 1) ? $remainingBreak : rand(10, $remainingBreak - ($breakCount - $j - 1) * 10);
                    $start = (clone $clockIn)->addMinutes(rand(120, 300)); // 出勤2〜5時間後に休憩
                    $end = (clone $start)->addMinutes($length);
                    $breaks[] = compact('start', 'end');
                    $remainingBreak -= $length;
                }

                // 退勤時間 = 出勤 + 勤務8時間 + 休憩時間
                $clockOut = (clone $clockIn)->addMinutes(480 + $breakTotalMinutes);

                // 勤務時間は休憩を引いてちょうど480分
                $attendance = Attendance::create([
                    'user_id'             => $user->id,
                    'work_date'           => $date->toDateString(),
                    'clock_in'            => $clockIn,
                    'clock_out'           => $clockOut,
                    'total_work_minutes'  => 480,
                ]);

                // 休憩データ保存
                foreach ($breaks as $b) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'start_time'    => $b['start'],
                        'end_time'      => $b['end'],
                    ]);
                }
            }
        }
    }
}
