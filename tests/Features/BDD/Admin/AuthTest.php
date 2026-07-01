<?php

namespace Tests\Features\BDD\Admin;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 管理者 — 認証
 * As a 管理者
 * I want メールアドレスとパスワードでログインしたい
 * So that 自組織のアンケート・参加者・お知らせを管理できる
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        // Given
        $org   = Organization::factory()->create();
        $admin = Admin::factory()->create([
            'organization_id' => $org->id,
            'email'           => 'admin@example.com',
            'password'        => bcrypt('password'),
        ]);

        // When
        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /** @test */
    public function ログアウトできる(): void
    {
        // Given
        $org   = Organization::factory()->create();
        $admin = Admin::factory()->create(['organization_id' => $org->id]);
        $this->actingAs($admin, 'admin');

        // When
        $response = $this->post('/admin/logout');

        // Then
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function メールアドレスが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/admin/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    /** @test */
    public function パスワードが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => '',
        ]);

        // Then
        $response->assertSessionHasErrors('password');
        $this->assertGuest('admin');
    }

    // ─── 異常系：認証失敗 ────────────────────────────────────────────────

    /** @test */
    public function 誤ったパスワードではログインできない(): void
    {
        // Given
        $org = Organization::factory()->create();
        Admin::factory()->create([
            'organization_id' => $org->id,
            'email'           => 'admin@example.com',
            'password'        => bcrypt('correct-password'),
        ]);

        // When
        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        // When
        $response = $this->post('/admin/login', [
            'email'    => 'notexist@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    /** @test */
    public function 無効化された管理者はログインできない(): void
    {
        // Given
        $org = Organization::factory()->create();
        Admin::factory()->create([
            'organization_id' => $org->id,
            'email'           => 'inactive@example.com',
            'password'        => bcrypt('password'),
            'is_active'       => false,
        ]);

        // When
        $response = $this->post('/admin/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 未認証でダッシュボードにアクセスするとログイン画面にリダイレクトされる(): void
    {
        // When
        $response = $this->get('/admin/dashboard');

        // Then
        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function 参加者アカウントでadminにアクセスできない(): void
    {
        // Given
        $participant = Participant::factory()->create();

        // When
        $response = $this->actingAs($participant, 'participant')->get('/admin/dashboard');

        // Then
        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function スーパー管理者アカウントでadminにアクセスできない(): void
    {
        // Given
        $super = Admin::factory()->super()->create();

        // When
        $response = $this->actingAs($super, 'super')->get('/admin/dashboard');

        // Then
        $response->assertRedirect('/admin/login');
    }
}
