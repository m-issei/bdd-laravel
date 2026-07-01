<?php

namespace Tests\Features\BDD\App;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 参加者 — お知らせ閲覧
 */
class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private Participant $participant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org         = Organization::factory()->create();
        $this->participant = Participant::factory()->create(['organization_id' => $this->org->id]);
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 公開中のお知らせが一覧に表示される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $announcement = Announcement::factory()->published()->create(['organization_id' => $this->org->id]);

        $response = $this->get('/app/announcements');

        $response->assertStatus(200);
        $response->assertSee($announcement->title);
    }

    /** @test */
    public function お知らせの詳細を閲覧できる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $announcement = Announcement::factory()->published()->create([
            'organization_id' => $this->org->id,
            'title'           => 'テストお知らせタイトル',
            'body'            => 'テストお知らせ本文',
        ]);

        $response = $this->get("/app/announcements/{$announcement->id}");

        $response->assertStatus(200);
        $response->assertSee('テストお知らせタイトル');
        $response->assertSee('テストお知らせ本文');
    }

    /** @test */
    public function 非公開のお知らせは一覧に表示されない(): void
    {
        $this->actingAs($this->participant, 'participant');
        $announcement = Announcement::factory()->create([
            'organization_id' => $this->org->id,
            'status'          => AnnouncementStatus::Draft,
        ]);

        $response = $this->get('/app/announcements');

        $response->assertStatus(200);
        $response->assertDontSee($announcement->title);
    }

    /** @test */
    public function 公開状態のお知らせのみ一覧に表示される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $published = Announcement::factory()->published()->create([
            'organization_id' => $this->org->id,
            'title'           => '公開お知らせ',
        ]);
        $draft = Announcement::factory()->create([
            'organization_id' => $this->org->id,
            'title'           => '非公開お知らせ',
            'status'          => AnnouncementStatus::Draft,
        ]);

        $response = $this->get('/app/announcements');

        $response->assertStatus(200);
        $response->assertSee('公開お知らせ');
        $response->assertDontSee('非公開お知らせ');
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 他組織のお知らせにアクセスすると404になる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $orgB         = Organization::factory()->create();
        $announcement = Announcement::factory()->published()->create(['organization_id' => $orgB->id]);

        $response = $this->get("/app/announcements/{$announcement->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function 存在しないお知らせIDにアクセスすると404になる(): void
    {
        $this->actingAs($this->participant, 'participant');

        $response = $this->get('/app/announcements/99999');

        $response->assertStatus(404);
    }
}
