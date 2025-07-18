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
        // 日付固定
        Carbon::setTestNow(Carbon::create(2025, 7, 14));

        // ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-07-14',
            'clock_in' => '2025-07-14 09:00:00',
            'clock_out' => '2025-07-14 18:00:00',
            'note' => 'テスト備考',
        ]);

        // 休憩時間作成（2回分なども可）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '2025-07-14 12:00:00',
            'end_time' => '2025-07-14 13:00:00',
        ]);

        // 認証してアクセス
        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        // 各種表示確認
        $response->assertStatus(200);
        $response->assertSee('山田太郎'); // 名前
        $response->assertSee('2025年7月14日'); // 日付（Viewでは format('Y年n月j日') ）
        $response->assertSee('09:00'); // 出勤
        $response->assertSee('18:00'); // 退勤
        $response->assertSee('12:00'); // 休憩開始
        $response->assertSee('13:00'); // 休憩終了
        $response->assertSee('テスト備考'); // 備考
    }
}
