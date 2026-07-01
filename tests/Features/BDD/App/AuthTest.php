<?php

namespace Tests\Features\BDD\App;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 参加者 — 認証
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::factory()->create();
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        // Given
        $participant = Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'participant@example.com',
            'password'        => bcrypt('password'),
        ]);

        // When
        $response = $this->post('/app/login', [
            'email'    => 'participant@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertRedirect('/app/surveys');
        $this->assertAuthenticatedAs($participant, 'participant');
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function メールアドレスが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/app/login', [
            'email'    => '',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('participant');
    }

    /** @test */
    public function パスワードが空の場合はログインできない(): void
    {
        // When
        $response = $this->post('/app/login', [
            'email'    => 'participant@example.com',
            'password' => '',
        ]);

        // Then
        $response->assertSessionHasErrors('password');
        $this->assertGuest('participant');
    }

    // ─── 異常系：認証失敗 ────────────────────────────────────────────────

    /** @test */
    public function 誤ったパスワードではログインできない(): void
    {
        // Given
        Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'participant@example.com',
            'password'        => bcrypt('correct-password'),
        ]);

        // When
        $response = $this->post('/app/login', [
            'email'    => 'participant@example.com',
            'password' => 'wrong-password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('participant');
    }

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        // When
        $response = $this->post('/app/login', [
            'email'    => 'notexist@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('participant');
    }

    /** @test */
    public function 無効化された参加者はログインできない(): void
    {
        // Given
        Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'inactive@example.com',
            'password'        => bcrypt('password'),
            'is_active'       => false,
        ]);

        // When
        $response = $this->post('/app/login', [
            'email'    => 'inactive@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('participant');
    }

    /** @test */
    public function 論理削除済みの参加者はログインできない(): void
    {
        // Given
        $participant = Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'deleted@example.com',
            'password'        => bcrypt('password'),
        ]);
        $participant->delete();

        // When
        $response = $this->post('/app/login', [
            'email'    => 'deleted@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertSessionHasErrors('email');
        $this->assertGuest('participant');
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 未認証でアンケート一覧にアクセスするとログイン画面にリダイレクトされる(): void
    {
        // When
        $response = $this->get('/app/surveys');

        // Then
        $response->assertRedirect('/app/login');
    }

    /** @test */
    public function 管理者アカウントでappにアクセスできない(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id]);

        // When
        $response = $this->actingAs($admin, 'admin')->get('/app/surveys');

        // Then
        $response->assertRedirect('/app/login');
    }
}
