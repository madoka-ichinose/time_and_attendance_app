<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function test_勤怠詳細に選択データが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->date,
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
            'note' => '勤務内容',
            'total_work_minutes' => 540,
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.attendance.detail', [
                'user_id' => $this->user->id,
                'work_date' => $this->date,
            ])
        );

        $response->assertStatus(200);
        $response->assertViewHas('attendance', function ($att) use ($attendance) {
            return $att->id === $attendance->id;
        });
    }

    public function test_出勤時間が退勤時間より後ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->date,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['_token' => 'test_token'])
            ->from('/admin/attendance/detail/' . $this->user->id . '/' . $this->date)
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    '_token' => 'test_token',
                    'user_id' => $this->user->id,
                    'work_date' => $this->date,
                    'clock_in' => '18:00',
                    'clock_out' => '09:00',
                    'note' => '遅刻',
                ]
            );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in']);
    }

    public function test_休憩開始が出勤時間より前ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->date,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['_token' => 'test_token'])
            ->from('/admin/attendance/detail/' . $this->user->id . '/' . $this->date)
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    '_token' => 'test_token',
                    'user_id' => $this->user->id,
                    'work_date' => $this->date,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'note' => '勤務',
                    'breaks' => [
                        ['start_time' => '08:00', 'end_time' => '09:30'],
                    ],
                ]
            );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['breaks.0.start_time']);
    }

    public function test_休憩終了が退勤時間より後ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->date,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['_token' => 'test_token'])
            ->from('/admin/attendance/detail/' . $this->user->id . '/' . $this->date)
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    '_token' => 'test_token',
                    'user_id' => $this->user->id,
                    'work_date' => $this->date,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'note' => '勤務',
                    'breaks' => [
                        ['start_time' => '17:00', 'end_time' => '19:00'],
                    ],
                ]
            );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['breaks.0.end_time']);
    }

    public function test_備考欄が未入力ならエラー()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $this->date,
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['_token' => 'test_token'])
            ->from('/admin/attendance/detail/' . $this->user->id . '/' . $this->date)
            ->put(
                route('admin.attendance.update', $attendance->id),
                [
                    '_token' => 'test_token',
                    'user_id' => $this->user->id,
                    'work_date' => $this->date,
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'note' => '',
                ]
            );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['note']);
    }
}
