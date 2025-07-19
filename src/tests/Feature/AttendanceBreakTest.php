<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    /** @test */
public function test_user_can_start_break_and_see_break_screen()
{
    Carbon::setTestNow(Carbon::create(2025, 7, 14, 10, 0));

    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today(),
        'clock_in' => Carbon::now(),
    ]);

    $response = $this->post(route('attendance.break'));
    $response->assertRedirect(route('attendance.break.screen'));

    $followUp = $this->get(route('attendance.break.screen'));
    $followUp->assertStatus(200);
    $followUp->assertViewIs('attendance.break');
    $followUp->assertSee('休憩中'); 
}

/** @test */
public function test_user_can_take_multiple_breaks_in_a_day()
{
    Carbon::setTestNow(Carbon::create(2025, 7, 14, 9, 0));

    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today(),
        'clock_in' => Carbon::now(),
    ]);

    $this->post(route('attendance.break'));
    $this->post(route('attendance.break.return'));

    Carbon::setTestNow(Carbon::now()->addHours(1));
    $this->post(route('attendance.break'));
    $this->post(route('attendance.break.return'));

    $this->assertCount(2, $attendance->refresh()->breaks);
}

/** @test */
public function test_user_can_end_break_and_return_to_working_screen()
{
    Carbon::setTestNow(Carbon::create(2025, 7, 14, 9, 0));

    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today(),
        'clock_in' => Carbon::now(),
    ]);

    $this->post(route('attendance.break'));

    $response = $this->post(route('attendance.break.return'));
    $response->assertRedirect(route('attendance.working'));

    $break = $attendance->refresh()->breaks()->latest()->first();
    $this->assertNotNull($break->end_time);
}

/** @test */
public function test_break_times_are_displayed_on_attendance_list()
{
    Carbon::setTestNow(Carbon::create(2025, 7, 14, 10, 49));

    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today(),
        'clock_in' => now()->subHours(4), 
        'clock_out' => now(),            
    ]);

    $attendance->breaks()->create([
        'start_time' => now()->subHours(2), 
        'end_time'   => now()->subHour(),   
    ]);

    $response = $this->get(route('attendance.list'));

    $response->assertStatus(200);
    $response->assertSee('01:00'); 
}

}
