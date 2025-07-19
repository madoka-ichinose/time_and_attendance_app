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
        Carbon::setTestNow(Carbon::create(2025, 7, 14, 10, 0)); 

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('attendance.start'));

        Carbon::setTestNow(Carbon::create(2025, 7, 14, 14, 30)); 

        $response = $this->post(route('attendance.end'));

        $response->assertRedirect(route('attendance.end.screen'));

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

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(4), 
            'clock_out' => Carbon::now(),             
            'total_work_minutes' => 240,
        ]);

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('10:00'); 
    }
}
