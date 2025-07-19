<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email_verified_at' => now(), 
        ]);
    }

    protected function postJsonWithCsrf(string $uri, array $data = [])
    {
        $token = 'test_csrf_token';

        return $this->withSession(['_token' => $token])
                    ->actingAs($this->user)
                    ->postJson($uri, $data, ['X-CSRF-TOKEN' => $token]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならエラーになる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '元データ'
        ]);

        $response = $this->postJsonWithCsrf(route('attendance.request.submit', ['id' => $attendance->id]), [
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'note' => 'テスト備考'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clock_in']);
    }

    /** @test */
    public function 休憩開始が出勤より前または退勤より後ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '元データ'
        ]);

        $response = $this->postJsonWithCsrf(route('attendance.request.submit', ['id' => $attendance->id]), [
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト備考',
            'breaks' => [
                ['start_time' => '08:00', 'end_time' => '10:00']
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.start_time']);
    }

    /** @test */
    public function 休憩終了が退勤より後ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '元データ'
        ]);

        $response = $this->postJsonWithCsrf(route('attendance.request.submit', ['id' => $attendance->id]), [
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => 'テスト備考',
            'breaks' => [
                ['start_time' => '10:00', 'end_time' => '19:00']
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.end_time']);
    }

    /** @test */
    public function 備考未入力ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '元データ'
        ]);

        $response = $this->postJsonWithCsrf(route('attendance.request.submit', ['id' => $attendance->id]), [
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['note']);
    }

    /** @test */
    public function 修正申請がDBに保存される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '元データ'
        ]);

        $response = $this->postJsonWithCsrf(route('attendance.request.submit', ['id' => $attendance->id]), [
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'note' => '修正申請',
            'breaks' => [
                ['start_time' => '12:00', 'end_time' => '13:00']
            ]
        ]);

        $response->assertRedirect(route('request.list'));
        $this->assertDatabaseHas('request_applications', [
            'reason' => '修正申請',
            'status' => '承認待ち',
        ]);
    }
}
