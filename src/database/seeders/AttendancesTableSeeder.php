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
        $daysCreated = 0;
        $date = Carbon::yesterday();

        while ($daysCreated < 90) {
            if ($date->isWeekend()) {
                $date->subDay();
                continue;
            }

            $clockIn = Carbon::createFromTime(rand(8, 9), rand(0, 59));

            $breakTotalMinutes = rand(45, 75);
            $breakCount = rand(1, 3);
            $breaks = [];
            $remainingBreak = $breakTotalMinutes;

            for ($j = 0; $j < $breakCount; $j++) {
                $length = ($j === $breakCount - 1) ? $remainingBreak : rand(10, $remainingBreak - ($breakCount - $j - 1) * 10);
                $start = (clone $clockIn)->addMinutes(rand(120, 300));
                $end = (clone $start)->addMinutes($length);
                $breaks[] = compact('start', 'end');
                $remainingBreak -= $length;
            }

            $clockOut = (clone $clockIn)->addMinutes(480 + $breakTotalMinutes);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'total_work_minutes' => 480,
            ]);

            foreach ($breaks as $b) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $b['start'],
                    'end_time' => $b['end'],
                ]);
            }

            $date->subDay();
            $daysCreated++;
        }
    }
}

}
