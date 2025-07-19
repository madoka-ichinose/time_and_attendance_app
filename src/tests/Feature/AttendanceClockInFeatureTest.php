<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AttendanceClockInFeatureTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /** @test */
public function clock_in_button_is_visible_on_start_screen_and_status_changes_after_clock_in()
{
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('attendance.show'));
    $response->assertStatus(200);
    $response->assertViewIs('attendance.start');
    $response->assertSee('出勤');

    $postResponse = $this->post(route('attendance.start'));
    $postResponse->assertRedirect(route('attendance.working')); 

    $responseAfter = $this->get(route('attendance.working'));
    $responseAfter->assertStatus(200);
    $responseAfter->assertViewIs('attendance.working');
    $responseAfter->assertSee('出勤中');
}


   /** @test */
public function clock_in_button_is_not_visible_after_clock_out()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    Attendance::create([
        'user_id'   => $user->id,
        'work_date' => Carbon::today()->toDateString(),
        'clock_in'  => Carbon::today()->setHours(9),
        'clock_out' => Carbon::today()->setHour(18), 
    ]);

    $response = $this->get(route('attendance.show'));

    $response->assertStatus(200);

    $response->assertViewIs('attendance.end');

    $response->assertDontSee('id="clock-in-button"', false);
}


    /** @test */
public function clock_in_time_is_displayed_in_attendance_list_after_clock_in()
{
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->post(route('attendance.start'))->assertStatus(302);

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', Carbon::today()->toDateString())
        ->first();

    $this->assertNotNull($attendance);
    $this->assertNotNull($attendance->clock_in);

    $clockInFormatted = Carbon::parse($attendance->clock_in)->format('H:i');

    $response = $this->get(route('attendance.list'));
    $response->assertStatus(200);

    $response->assertSee($clockInFormatted);
}


    /** @test */
public function clock_in_button_is_not_shown_if_already_clocked_in_and_cannot_clock_in_twice()
{
    $user = User::factory()->create();

    Attendance::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
        'clock_in' => Carbon::now()->subHour(),
        'clock_out' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('attendance.show'));
    $response->assertStatus(200);
    $response->assertDontSee('id="clock-in-button"', false);

    $postResponse = $this->post(route('attendance.start'));
    $postResponse->assertRedirect(route('attendance.working'));

    $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', Carbon::today()->toDateString())
        ->first();

    $this->assertNotNull($attendance->clock_in);
    $this->assertTrue($attendance->clock_in->lessThan(Carbon::now()));
}

}
