<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceDetailFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に必要な情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 14));

        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-14',
            'clock_in' => '2025-07-14 09:00:00',
            'clock_out' => '2025-07-14 18:00:00',
            'note' => 'テスト備考',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-07-14 12:00:00',
            'end_time' => '2025-07-14 13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('山田太郎'); 
        $response->assertSee('2025年7月14日'); 
        $response->assertSee('09:00'); 
        $response->assertSee('18:00'); 
        $response->assertSee('12:00'); 
        $response->assertSee('13:00'); 
        $response->assertSee('テスト備考'); 
    }
}
