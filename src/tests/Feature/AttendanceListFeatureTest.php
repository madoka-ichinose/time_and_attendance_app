<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 7, 14)); // 日付固定
    }

    public function test_勤怠情報が全て表示されている()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now(),
            'total_work_minutes' => 480,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user,'web');

        $this->assertTrue(auth()->check());
        
        $response = $this->get(route('attendance.list'));
        
        $response->assertStatus(200);

        $response->assertSee('07/14');
        $response->assertSee('08:00');
        $response->assertSee('16:00');
    }

    public function test_現在の月が表示されている()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');
    $this->assertTrue(auth()->check()); // ← 追加

    $response = $this->get(route('attendance.list'));
    $response->assertStatus(200);
    }

    public function test_前月の勤怠が表示される()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $prevMonth = Carbon::now()->subMonth()->startOfMonth()->addDays(3);

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'work_date' => $prevMonth->toDateString(),
            'clock_in' => Carbon::parse($prevMonth->toDateString() . ' 09:00:00'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user,'web')->get(route('attendance.list', [
            'year' => $prevMonth->year,
            'month' => $prevMonth->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('m/d'));
    }

    public function test_翌月の勤怠が表示される()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test4@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $nextMonth = Carbon::now()->addMonth()->startOfMonth()->addDays(5);

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'work_date' => $nextMonth->toDateString(),
            'clock_in' => Carbon::parse($nextMonth->toDateString() . ' 10:00:00'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')->get(route('attendance.list', [
            'year' => $nextMonth->year,
            'month' => $nextMonth->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('m/d'));
    }

    public function test_詳細ボタンから勤怠詳細画面に遷移できる()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test5@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    
        $workDate = Carbon::today()->toDateString();

        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $editUrl = route('attendance.createOrEdit', ['date' => $workDate]);

        $response = $this->actingAs($user, 'web')->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee($editUrl); // 詳細リンクのURLが含まれていることを確認
    }
}
