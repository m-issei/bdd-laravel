<?php

namespace Tests\Features\BDD\Super;

use App\Models\Admin;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: スーパー管理者 — 組織管理
 * As a スーパー管理者
 * I want 組織を作成・編集・削除したい
 * So that マルチテナントの組織単位でシステムを管理できる
 */
class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = Admin::factory()->super()->create();
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 有効な組織名で組織を作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/organizations', ['name' => 'テスト組織']);

        // Then
        $response->assertRedirect('/super/organizations');
        $this->assertDatabaseHas('organizations', ['name' => 'テスト組織']);
    }

    /** @test */
    public function 組織名と説明を編集できる(): void
    {
        // Given
        $org = Organization::factory()->create(['name' => '旧名称']);

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put("/super/organizations/{$org->id}", [
                'name'        => '新名称',
                'description' => '説明文',
            ]);

        // Then
        $response->assertRedirect('/super/organizations');
        $this->assertDatabaseHas('organizations', ['id' => $org->id, 'name' => '新名称', 'description' => '説明文']);
    }

    /** @test */
    public function 組織を論理削除すると一覧から消える(): void
    {
        // Given
        $org = Organization::factory()->create();

        // When
        $this->actingAs($this->superAdmin, 'super')
            ->delete("/super/organizations/{$org->id}");

        // Then
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get('/super/organizations');
        $response->assertDontSee($org->name);
    }

    /** @test */
    public function 論理削除した組織のレコードはDBに残る(): void
    {
        // Given
        $org = Organization::factory()->create();

        // When
        $this->actingAs($this->superAdmin, 'super')
            ->delete("/super/organizations/{$org->id}");

        // Then
        $this->assertSoftDeleted('organizations', ['id' => $org->id]);
    }

    /** @test */
    public function 組織一覧を取得できる(): void
    {
        // Given
        $orgs = Organization::factory()->count(3)->create();

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->get('/super/organizations');

        // Then
        $response->assertOk();
        foreach ($orgs as $org) {
            $response->assertSee($org->name);
        }
    }

    // ─── 異常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 組織名が空の場合は作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/organizations', ['name' => '']);

        // Then
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('organizations', 0);
    }

    /** @test */
    public function 存在しない組織IDへのアクセスは404を返す(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put('/super/organizations/99999', ['name' => '名称']);

        // Then
        $response->assertNotFound();
    }

    /** @test */
    public function 未認証では組織一覧にアクセスできない(): void
    {
        // When
        $response = $this->get('/super/organizations');

        // Then
        $response->assertRedirect('/super/login');
    }

    // ─── 境界値テスト ─────────────────────────────────────────────────────

    /** @test */
    public function 組織名が255文字の場合は作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/organizations', ['name' => str_repeat('あ', 255)]);

        // Then
        $response->assertRedirect('/super/organizations');
        $this->assertDatabaseCount('organizations', 1);
    }

    /** @test */
    public function 組織名が256文字の場合は作成できない(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/organizations', ['name' => str_repeat('あ', 256)]);

        // Then
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('organizations', 0);
    }

    /** @test */
    public function 組織名が1文字の場合は作成できる(): void
    {
        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->post('/super/organizations', ['name' => 'A']);

        // Then
        $response->assertRedirect('/super/organizations');
        $this->assertDatabaseCount('organizations', 1);
    }

    // ─── 状態・パターン網羅 ───────────────────────────────────────────────

    /** @test */
    public function 論理削除済み組織は編集できない(): void
    {
        // Given
        $org = Organization::factory()->create();
        $org->delete();

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->put("/super/organizations/{$org->id}", ['name' => '新名称']);

        // Then
        $response->assertNotFound();
    }

    /** @test */
    public function 論理削除済み組織は再削除できない(): void
    {
        // Given
        $org = Organization::factory()->create();
        $org->delete();

        // When
        $response = $this->actingAs($this->superAdmin, 'super')
            ->delete("/super/organizations/{$org->id}");

        // Then
        $response->assertNotFound();
    }
}
