<?php

namespace Tests\Features\BDD\Admin;

use App\Enums\AnnouncementStatus;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 管理者 — お知らせ管理
 */
class AnnouncementTest extends TestCase
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
    public function お知らせを新規作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => 'テストお知らせ',
            'body'  => 'テスト本文',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('announcements', [
            'title'           => 'テストお知らせ',
            'organization_id' => $this->org->id,
        ]);
    }

    /** @test */
    public function お知らせを公開状態にできる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $announcement = Announcement::factory()->create([
            'organization_id' => $this->org->id,
            'status'          => AnnouncementStatus::Draft,
        ]);

        $response = $this->patchJson("/admin/announcements/{$announcement->id}/toggle-status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('announcements', [
            'id'     => $announcement->id,
            'status' => AnnouncementStatus::Published->value,
        ]);
    }

    /** @test */
    public function 公開中のお知らせを非公開にできる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $announcement = Announcement::factory()->published()->create([
            'organization_id' => $this->org->id,
        ]);

        $response = $this->patchJson("/admin/announcements/{$announcement->id}/toggle-status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('announcements', [
            'id'     => $announcement->id,
            'status' => AnnouncementStatus::Draft->value,
        ]);
    }

    /** @test */
    public function お知らせを編集できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $announcement = Announcement::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->putJson("/admin/announcements/{$announcement->id}", [
            'title' => '更新タイトル',
            'body'  => '更新本文',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('announcements', [
            'id'    => $announcement->id,
            'title' => '更新タイトル',
            'body'  => '更新本文',
        ]);
    }

    /** @test */
    public function お知らせを削除できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $announcement = Announcement::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->deleteJson("/admin/announcements/{$announcement->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);
    }

    /** @test */
    public function 同一タイトルのお知らせを複数作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        Announcement::factory()->create(['organization_id' => $this->org->id, 'title' => '同じタイトル']);

        $response = $this->postJson('/admin/announcements', [
            'title' => '同じタイトル',
            'body'  => '本文',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('announcements', 2);
    }

    /** @test */
    public function タイトルが255文字の場合は作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => str_repeat('a', 255),
            'body'  => '本文',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function 本文が10000文字の場合は作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => 'タイトル',
            'body'  => str_repeat('あ', 10000),
        ]);

        $response->assertStatus(201);
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function タイトルが空の場合はお知らせを作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => '',
            'body'  => '本文',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 本文が空の場合はお知らせを作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => 'タイトル',
            'body'  => '',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function タイトルが256文字の場合は作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => str_repeat('a', 256),
            'body'  => '本文',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 本文が10001文字の場合は作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/announcements', [
            'title' => 'タイトル',
            'body'  => str_repeat('あ', 10001),
        ]);

        $response->assertStatus(422);
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 他組織のお知らせにアクセスすると404になる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $orgB         = Organization::factory()->create();
        $announcement = Announcement::factory()->create(['organization_id' => $orgB->id]);

        $response = $this->putJson("/admin/announcements/{$announcement->id}", [
            'title' => '不正な更新',
            'body'  => '不正な本文',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function 存在しないお知らせIDにアクセスすると404になる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->putJson('/admin/announcements/99999', [
            'title' => '不正な更新',
            'body'  => '不正な本文',
        ]);

        $response->assertStatus(404);
    }

    // ─── App側のお知らせ表示テスト ───────────────────────────────────────

    /** @test */
    public function 公開状態のお知らせは参加者に表示される(): void
    {
        $participant  = Participant::factory()->create(['organization_id' => $this->org->id]);
        $announcement = Announcement::factory()->published()->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($participant, 'participant')->get('/app/announcements');

        $response->assertStatus(200);
        $response->assertSee($announcement->title);
    }

    /** @test */
    public function 非公開状態のお知らせは参加者に表示されない(): void
    {
        $participant  = Participant::factory()->create(['organization_id' => $this->org->id]);
        $announcement = Announcement::factory()->create([
            'organization_id' => $this->org->id,
            'status'          => AnnouncementStatus::Draft,
        ]);

        $response = $this->actingAs($participant, 'participant')->get('/app/announcements');

        $response->assertStatus(200);
        $response->assertDontSee($announcement->title);
    }
}
