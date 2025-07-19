<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendancePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_attendance_page_with_current_datetime()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200); 

        $response->assertSeeInOrder([
            Carbon::now()->format('Y年n月j日'), 
            Carbon::now()->format('H:i'),      
        ]);
    }
}
