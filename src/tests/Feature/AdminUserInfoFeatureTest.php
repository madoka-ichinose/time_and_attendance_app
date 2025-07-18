<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserInfoFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }

    public function test_スタッフ一覧でユーザー情報が表示される()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('一般ユーザー');
        $response->assertSee('user@example.com');
    }

    public function test_勤怠一覧にユーザーの勤怠が正しく表示される()
    {
        $date = Carbon::create(2025, 7, 1);
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0),
            'clock_out' => $date->copy()->setTime(18, 0),
            'note' => '出勤',
            'total_work_minutes' => 540,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.monthly', $this->user->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月ボタンで前月の勤怠情報が表示される()
    {
        $prevMonth = Carbon::now()->subMonth()->startOfMonth();
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $prevMonth->toDateString(),
            'note' => '前月出勤',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.attendance.monthly', [
                'user' => $this->user->id,
                'month' => $prevMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('m/d'));
    }

    public function test_翌月ボタンで翌月の勤怠情報が表示される()
    {
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $nextMonth->toDateString(),
            'note' => '翌月出勤',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.attendance.monthly', [
                'user' => $this->user->id,
                'month' => $nextMonth->format('Y-m')
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('m/d'));
    }

    public function test_勤怠詳細画面に遷移できる()
    {
        $workDate = Carbon::create(2025, 7, 1)->toDateString();

        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => $workDate,
            'note' => '勤怠詳細用',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.attendance.detail', [
                'user_id' => $this->user->id,
                'work_date' => $workDate,
            ])
        );

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
