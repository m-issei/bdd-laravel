<?php

namespace Tests\Features\BDD\Admin;

use App\Models\Admin;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Response;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 管理者 — 参加者管理
 * As a 管理者
 * I want 参加者を作成・編集・削除したい
 * So that 自組織のアンケートに回答できる参加者を管理できる
 */
class ParticipantTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org   = Organization::factory()->create();
        $this->admin = Admin::factory()->create(['organization_id' => $this->org->id]);
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 参加者を新規作成できる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->post('/admin/participants', [
            'name'     => 'テスト参加者',
            'email'    => 'participant@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(201);
        $this->assertDatabaseHas('participants', [
            'name'            => 'テスト参加者',
            'email'           => 'participant@example.com',
            'organization_id' => $this->org->id,
        ]);
    }

    /** @test */
    public function 参加者を編集できる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        $participant = Participant::factory()->create(['organization_id' => $this->org->id]);

        // When
        $response = $this->put("/admin/participants/{$participant->id}", [
            'name'  => '更新後の名前',
            'email' => $participant->email,
        ]);

        // Then
        $response->assertStatus(200);
        $this->assertDatabaseHas('participants', [
            'id'   => $participant->id,
            'name' => '更新後の名前',
        ]);
    }

    /** @test */
    public function 参加者を論理削除すると削除済みフラグが立つ(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        $participant = Participant::factory()->create(['organization_id' => $this->org->id]);

        // When
        $response = $this->delete("/admin/participants/{$participant->id}");

        // Then
        $response->assertStatus(200);
        $this->assertSoftDeleted('participants', ['id' => $participant->id]);
    }

    /** @test */
    public function 参加者を無効化できる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        $participant = Participant::factory()->create([
            'organization_id' => $this->org->id,
            'is_active'       => true,
        ]);

        // When
        $response = $this->patch("/admin/participants/{$participant->id}/toggle-active");

        // Then
        $response->assertStatus(200);
        $this->assertDatabaseHas('participants', [
            'id'        => $participant->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function 別組織で同じメールアドレスの参加者を作成できる(): void
    {
        // Given
        $orgB = Organization::factory()->create();
        Participant::factory()->create([
            'organization_id' => $orgB->id,
            'email'           => 'shared@example.com',
        ]);
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->post('/admin/participants', [
            'name'     => '参加者A',
            'email'    => 'shared@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(201);
        $this->assertDatabaseCount('participants', 2);
    }

    /** @test */
    public function 削除済み参加者は一覧で削除済みとして識別できる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        $participant = Participant::factory()->create(['organization_id' => $this->org->id]);
        $participant->delete();

        // When
        $response = $this->get('/admin/participants');

        // Then
        $response->assertStatus(200);
        $response->assertSee('削除済み');
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function 名前が空の場合は参加者を作成できない(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->postJson('/admin/participants', [
            'name'     => '',
            'email'    => 'participant@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(422);
    }

    /** @test */
    public function メールアドレスが空の場合は作成できない(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->postJson('/admin/participants', [
            'name'     => 'テスト参加者',
            'email'    => '',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(422);
    }

    /** @test */
    public function メールアドレスが不正な形式の場合は作成できない(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->postJson('/admin/participants', [
            'name'     => 'テスト参加者',
            'email'    => 'not-an-email',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(422);
    }

    /** @test */
    public function 同一組織内でメールアドレスが重複する参加者は作成できない(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'duplicate@example.com',
        ]);

        // When
        $response = $this->postJson('/admin/participants', [
            'name'     => '別の参加者',
            'email'    => 'duplicate@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(422);
    }

    /** @test */
    public function 名前が100文字の場合は作成できる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->post('/admin/participants', [
            'name'     => str_repeat('あ', 100),
            'email'    => 'participant@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(201);
    }

    /** @test */
    public function 名前が101文字の場合は作成できない(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->postJson('/admin/participants', [
            'name'     => str_repeat('あ', 101),
            'email'    => 'participant@example.com',
            'password' => 'password123',
        ]);

        // Then
        $response->assertStatus(422);
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 他組織の参加者を編集しようとすると404になる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');
        $orgB        = Organization::factory()->create();
        $participant = Participant::factory()->create(['organization_id' => $orgB->id]);

        // When
        $response = $this->put("/admin/participants/{$participant->id}", [
            'name'  => '不正な更新',
            'email' => $participant->email,
        ]);

        // Then
        $response->assertStatus(404);
    }

    /** @test */
    public function 存在しない参加者IDにアクセスすると404になる(): void
    {
        // Given
        $this->actingAs($this->admin, 'admin');

        // When
        $response = $this->put('/admin/participants/99999', [
            'name'  => 'テスト',
            'email' => 'test@example.com',
        ]);

        // Then
        $response->assertStatus(404);
    }

    // ─── App側の参加者認証テスト ─────────────────────────────────────────

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
    public function 再有効化された参加者はログインできる(): void
    {
        // Given
        $participant = Participant::factory()->create([
            'organization_id' => $this->org->id,
            'email'           => 'reactive@example.com',
            'password'        => bcrypt('password'),
            'is_active'       => false,
        ]);
        $participant->update(['is_active' => true]);

        // When
        $response = $this->post('/app/login', [
            'email'    => 'reactive@example.com',
            'password' => 'password',
        ]);

        // Then
        $response->assertRedirect('/app/surveys');
        $this->assertAuthenticatedAs($participant, 'participant');
    }
}
