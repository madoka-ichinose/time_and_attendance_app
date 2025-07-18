<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RequestApplication;
use App\Models\RequestBreakTime;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;
    protected $requestApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => '管理者',
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

        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2025-07-01',
            'clock_in' => '2025-07-01 09:00:00',
            'clock_out' => '2025-07-01 18:00:00',
            'note' => '元データ',
        ]);

        $this->requestApp = RequestApplication::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'clock_in' => '2025-07-01 09:30:00',
            'clock_out' => '2025-07-01 18:00:00',
            'reason' => '修正理由です',
            'applied_at' => now(),
            'status' => '承認待ち',
            'work_date' => '2025-07-01',
        ]);
    }

    public function test_承認待ちの修正申請一覧が表示される()
    {
        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/list?status=承認待ち');

        $response->assertStatus(200);
        $response->assertSee('修正理由です');
    }

    public function test_承認済みの修正申請一覧が表示される()
    {
        $this->requestApp->update(['status' => '承認済み']);

        $response = $this->actingAs($this->admin)->get('/admin/stamp_correction_request/list?status=承認済み');

        $response->assertStatus(200);
        $response->assertSee('修正理由です');
    }

    public function test_修正申請の詳細が表示される()
    {
        $response = $this->actingAs($this->admin)->get("/admin/requests/{$this->requestApp->id}/detail");

        $response->assertStatus(200);
        $response->assertSee('修正理由です');
        $response->assertSee('09:30');
    }

    public function test_修正申請を承認すると勤怠情報が更新される()
{
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    $this->withoutExceptionHandling();

    $response = $this->actingAs($this->admin)->put("/admin/stamp_correction_request/approve/{$this->requestApp->id}");

    $response->assertRedirect("/admin/requests/{$this->requestApp->id}/detail");

    $this->assertDatabaseHas('attendances', [
        'id' => $this->attendance->id,
        'clock_in' => '2025-07-01 09:30:00',
    ]);

    $this->assertDatabaseHas('request_applications', [
        'id' => $this->requestApp->id,
        'status' => '承認済み',
    ]);
}

}
