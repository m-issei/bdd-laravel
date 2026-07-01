<?php

namespace Tests\Features\BDD\Super;

use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: スーパー管理者 — 管理者アカウント管理
 * As a スーパー管理者
 * I want 各組織の管理者アカウントを作成・管理したい
 * So that 組織ごとに適切な管理者を割り当てられる
 */
class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = Admin::factory()->super()->create();
        $this->org        = Organization::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'organization_id' => $this->org->id,
            'name'            => 'テスト管理者',
            'email'           => 'admin@example.com',
            'password'        => 'password1',
        ], $overrides);
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 組織を指定して管理者アカウントを作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload());

        // Then
        $response->assertRedirect('/super/admins');
        $this->assertDatabaseHas('admins', [
            'email'           => 'admin@example.com',
            'organization_id' => $this->org->id,
            'is_super'        => false,
        ]);
    }

    /** @test */
    public function 管理者情報を編集できる(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id]);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put("/super/admins/{$admin->id}", [
                'organization_id' => $this->org->id,
                'name'            => '更新後管理者',
                'email'           => 'updated@example.com',
            ]);

        // Then
        $response->assertRedirect('/super/admins');
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'name' => '更新後管理者']);
    }

    /** @test */
    public function 管理者を無効化できる(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id, 'is_active' => true]);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->patch("/super/admins/{$admin->id}/toggle-active", ['is_active' => false]);

        // Then
        $response->assertRedirect('/super/admins');
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'is_active' => false]);
    }

    /** @test */
    public function 無効化した管理者を再有効化できる(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id, 'is_active' => false]);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->patch("/super/admins/{$admin->id}/toggle-active", ['is_active' => true]);

        // Then
        $response->assertRedirect('/super/admins');
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'is_active' => true]);
    }

    /** @test */
    public function 管理者を論理削除すると一覧から消えDBにレコードが残る(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id]);

        // When
        $this->actingAs($this->superAdmin, 'super')
            ->delete("/super/admins/{$admin->id}");

        // Then
        $this->assertSoftDeleted('admins', ['id' => $admin->id]);
        $response = $this->actingAs($this->superAdmin, 'super')->get('/super/admins');
        $response->assertDontSee($admin->email);
    }

    /** @test */
    public function 管理者一覧を取得できる(): void
    {
        // Given
        $admins = Admin::factory()->count(3)->create(['organization_id' => $this->org->id]);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')->get('/super/admins');

        // Then
        $response->assertOk();
        foreach ($admins as $admin) {
            $response->assertSee($admin->email);
        }
    }

    // ─── 異常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function メールアドレスが空の場合は作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['email' => '']));

        // Then
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 不正な形式のメールアドレスでは作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['email' => 'not-an-email']));

        // Then
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 同一組織内でメールアドレスが重複する場合は作成できない(): void
    {
        // Given
        Admin::factory()->create(['organization_id' => $this->org->id, 'email' => 'dup@example.com']);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['email' => 'dup@example.com']));

        // Then
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function 存在しない管理者IDへの操作は404を返す(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put('/super/admins/99999', $this->validPayload());

        // Then
        $response->assertNotFound();
    }

    /** @test */
    public function 未認証では管理者一覧にアクセスできない(): void
    {
        // When
        $response = $this->get('/super/admins');

        // Then
        $response->assertRedirect('/super/login');
    }

    // ─── 境界値テスト ─────────────────────────────────────────────────────

    /** @test */
    public function 管理者名が100文字の場合は作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['name' => str_repeat('あ', 100)]));

        // Then
        $response->assertRedirect('/super/admins');
        $this->assertDatabaseCount('admins', 2); // superAdmin + new
    }

    /** @test */
    public function 管理者名が101文字の場合は作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['name' => str_repeat('あ', 101)]));

        // Then
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function パスワードが8文字の場合は作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['password' => '12345678']));

        // Then
        $response->assertRedirect('/super/admins');
    }

    /** @test */
    public function パスワードが7文字の場合は作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/admins', $this->validPayload(['password' => '1234567']));

        // Then
        $response->assertSessionHasErrors('password');
    }

    // ─── 状態・パターン網羅 ───────────────────────────────────────────────

    /** @test */
    public function 無効化された管理者は管理者ログインできない(): void
    {
        // Given
        Admin::factory()->create([
            'organization_id' => $this->org->id,
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

    /** @test */
    public function 再有効化された管理者は管理者ログインできる(): void
    {
        // Given
        $admin = Admin::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'reactive@example.com',
            'password'        => bcrypt('password'),
            'is_active'       => true,
        ]);

        // When
        $response = $this->post('/admin/login', [
            'email'    => 'reactive@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    /** @test */
    public function 論理削除済み管理者は編集できない(): void
    {
        // Given
        $admin = Admin::factory()->create(['organization_id' => $this->org->id]);
        $admin->delete();

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put("/super/admins/{$admin->id}", $this->validPayload());

        // Then
        $response->assertNotFound();
    }
}
