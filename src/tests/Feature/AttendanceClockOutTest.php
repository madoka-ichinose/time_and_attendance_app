<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_clock_out_successfully()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 10, 0)); // 出勤時刻

        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.start'));

        // 時刻を進める（勤務中にする）
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 14, 30)); // 退勤時刻

        // 退勤処理
        $response = $this->post(route('attendance.end'));

        // ステータス確認（画面遷移）
        $response->assertRedirect(route('attendance.end.screen'));

        // データ確認
        $attendance = Attendance::where('user_id', $user->id)->first();

        $this->assertNotNull($attendance->clock_out);
        $this->assertEquals('2025-07-14 14:30:00', $attendance->clock_out->format('Y-m-d H:i:s'));
        $this->assertGreaterThan(0, $attendance->total_work_minutes);
    }

    /** @test */
    public function clock_out_time_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 10, 0));

        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤・退勤を事前に登録
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(4), // 6:00
            'clock_out' => Carbon::now(),             // 10:00
            'total_work_minutes' => 240,
        ]);

        // 一覧画面を表示
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('10:00'); // 退勤時刻が表示されていることを確認
    }
}
