<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * この掲示板は匿名で書き込めるので、利用者の登録は行わない。
     * ログインは運営者（モデレーション）専用で、routes/auth.php の
     * 登録ルートは意図的に閉じてある。開いてしまうと誰でも管理画面の
     * 入口にアカウントを作れてしまうため、閉じたままであることを試験する。
     */
    public function test_利用者の新規登録は閉じている(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
