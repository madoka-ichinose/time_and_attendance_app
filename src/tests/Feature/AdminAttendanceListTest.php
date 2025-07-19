<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        Carbon::setTestNow(Carbon::create(2025, 7, 18, 9, 0, 0));

        $users = User::where('role', 'user')->get();
        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => Carbon::today()->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'total_work_minutes' => 540
            ]);
        }
    }

    /**
     * @test
     */
    public function 管理者が当日の全ユーザーの勤怠情報を確認できる()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->toDateString()); 
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * @test
     */
    public function 勤怠一覧画面で現在日付が表示される()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertSee(Carbon::today()->toDateString());
    }

    /**
     * @test
     */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = User::where('role', 'admin')->first();

        $date = Carbon::yesterday()->toDateString();

        $users = User::where('role', 'user')->get();
        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'total_work_minutes' => 540
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     * @test
     */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = User::where('role', 'admin')->first();

        $date = Carbon::tomorrow()->toDateString();

        $users = User::where('role', 'user')->get();
        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '08:30:00',
                'clock_out' => '17:30:00',
                'total_work_minutes' => 540
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }
}
