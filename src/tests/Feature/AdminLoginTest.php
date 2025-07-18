<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_required_for_admin_login()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertRedirect(); // フォームにリダイレクトされる想定
    }

    /** @test */
    public function password_is_required_for_admin_login()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertRedirect();
    }

    /** @test */
    public function admin_login_fails_with_incorrect_credentials()
    {
        // 管理者レコード（正しいパスワード）を作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        // 誤ったパスワードでログイン
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']); // メールアドレスキーにエラーを返す形式
        $response->assertRedirect('/admin/login');

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。'
        ]);
    }
}
