<?php

namespace Tests\Features\BDD\Super;

use App\Models\Admin;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: スーパー管理者 — 認証
 * As a スーパー管理者
 * I want メールアドレスとパスワードでログインしたい
 * So that システム全体の管理画面にアクセスできる
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        // Given
        $admin = Admin::factory()->super()->create([
            'email'    => 'super@example.com',
            'password' => bcrypt('password'),
        ]);

        // When
        $response = $this->post('/super/login', [
            'email'    => 'super@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertRedirect('/super/organizations');
        $this->assertAuthenticatedAs($admin, 'super');
    }

    /** @test */
    public function ログアウトできる(): void
    {
        // Given
        $admin = Admin::factory()->super()->create();
        $this->actingAs($admin, 'super');

        // When
        $response = $this->post('/super/logout');

        // Then
        $response->assertRedirect('/super/login');
        $this->assertGuest('super');
    }

    /** @test */
    public function ログイン済みの状態でログインページにアクセスするとトップにリダイレクトされる(): void
    {
        // Given
        $admin = Admin::factory()->super()->create();
        $this->actingAs($admin, 'super');

        // When
        $response = $this->get('/super/login');

        // Then
        $response->assertRedirect('/super/organizations');
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function メールアドレスが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/super/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('super');
    }

    /** @test */
    public function パスワードが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/super/login', [
            'email'    => 'super@example.com',
            'password' => '',
        ]);

        // Then
        $response->assertSessionHasErrors('password');
        $this->assertGuest('super');
    }

    /** @test */
    public function 不正な形式のメールアドレスではログインできない(): void
    {
        // When
        $response = $this->post('/super/login', [
            'email'    => 'not-an-email',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('super');
    }

    // ─── 異常系：認証失敗 ────────────────────────────────────────────────

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        // Given
        Admin::factory()->super()->create(['email' => 'super@example.com']);

        // When
        $response = $this->post('/super/login', [
            'email'    => 'notexist@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('super');
    }

    /** @test */
    public function 誤ったパスワードではログインできない(): void
    {
        // Given
        Admin::factory()->super()->create([
            'email'    => 'super@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // When
        $response = $this->post('/super/login', [
            'email'    => 'super@example.com',
            'password' => 'wrong-password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('super');
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 未認証で_super_organizationsにアクセスするとログイン画面にリダイレクトされる(): void
    {
        // When
        $response = $this->get('/super/organizations');

        // Then
        $response->assertRedirect('/super/login');
    }

    /** @test */
    public function 管理者アカウントで_superにアクセスできない(): void
    {
        // Given
        $admin = Admin::factory()->create(['is_super' => false]);

        // When
        $response = $this->actingAs($admin, 'admin')->get('/super/organizations');

        // Then
        $response->assertRedirect('/super/login');
    }

    /** @test */
    public function 参加者アカウントで_superにアクセスできない(): void
    {
        // Given
        $participant = Participant::factory()->create();

        // When
        $response = $this->actingAs($participant, 'participant')->get('/super/organizations');

        // Then
        $response->assertRedirect('/super/login');
    }
}
