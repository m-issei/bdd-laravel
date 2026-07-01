<?php

namespace Tests\Features\BDD\Admin;

use App\Enums\SurveyStatus;
use App\Models\Admin;
use App\Models\AnswerType;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Survey;
use App\Models\SurveySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 管理者 — アンケート管理
 */
class SurveyTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private Admin $admin;
    private AnswerType $answerType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org        = Organization::factory()->create();
        $this->admin      = Admin::factory()->create(['organization_id' => $this->org->id]);
        $this->answerType = AnswerType::firstOrCreate(['name' => 'radio_1_5'], ['label' => '1〜5段階']);
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function アンケートを下書きで作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/surveys', ['title' => 'テストアンケート']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('surveys', [
            'title'           => 'テストアンケート',
            'status'          => SurveyStatus::Draft->value,
            'organization_id' => $this->org->id,
        ]);
    }

    /** @test */
    public function 同一タイトルのアンケートを複数作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        Survey::factory()->create(['organization_id' => $this->org->id, 'title' => '重複タイトル']);

        $response = $this->postJson('/admin/surveys', ['title' => '重複タイトル']);

        $response->assertStatus(201);
        $this->assertDatabaseCount('surveys', 2);
    }

    /** @test */
    public function 下書きのアンケートを編集できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->putJson("/admin/surveys/{$survey->id}", ['title' => '更新タイトル']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'title' => '更新タイトル']);
    }

    /** @test */
    public function 質問が1問以上あるアンケートを公開できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);
        Question::factory()->create([
            'survey_id'      => $survey->id,
            'answer_type_id' => $this->answerType->id,
        ]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/publish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('surveys', [
            'id'     => $survey->id,
            'status' => SurveyStatus::Published->value,
        ]);
    }

    /** @test */
    public function 見出しを削除すると中の質問はsection_idがnullで残る(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey  = Survey::factory()->create(['organization_id' => $this->org->id]);
        $section = SurveySection::factory()->create(['survey_id' => $survey->id]);
        $question = Question::factory()->create([
            'survey_id'         => $survey->id,
            'survey_section_id' => $section->id,
            'answer_type_id'    => $this->answerType->id,
        ]);

        $response = $this->deleteJson("/admin/surveys/{$survey->id}/sections/{$section->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('survey_sections', ['id' => $section->id]);
        $this->assertDatabaseHas('questions', [
            'id'                => $question->id,
            'survey_section_id' => null,
        ]);
    }

    /** @test */
    public function アンケートを論理削除できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->deleteJson("/admin/surveys/{$survey->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('surveys', ['id' => $survey->id]);
    }

    /** @test */
    public function タイトルが255文字の場合は作成できる(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/surveys', ['title' => str_repeat('a', 255)]);

        $response->assertStatus(201);
    }

    /** @test */
    public function 質問文が500文字の場合は保存できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/questions", [
            'text'           => str_repeat('あ', 500),
            'answer_type_id' => $this->answerType->id,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function 見出しタイトルが100文字の場合は保存できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/sections", [
            'title' => str_repeat('a', 100),
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function 下書き→公開の状態遷移は成功する(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);
        Question::factory()->create([
            'survey_id'      => $survey->id,
            'answer_type_id' => $this->answerType->id,
        ]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/publish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'status' => SurveyStatus::Published->value]);
    }

    // ─── 異常系：バリデーション ──────────────────────────────────────────

    /** @test */
    public function タイトルが空の場合はアンケートを作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/surveys', ['title' => '']);

        $response->assertStatus(422);
    }

    /** @test */
    public function 質問が0件のアンケートは公開できない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/publish");

        $response->assertStatus(422);
    }

    /** @test */
    public function 同一アンケート内で見出しタイトルが重複する場合は保存できない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);
        SurveySection::factory()->create(['survey_id' => $survey->id, 'title' => '重複見出し']);

        $response = $this->postJson("/admin/surveys/{$survey->id}/sections", ['title' => '重複見出し']);

        $response->assertStatus(422);
    }

    /** @test */
    public function 公開済みアンケートの質問は編集できない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create([
            'survey_id'      => $survey->id,
            'answer_type_id' => $this->answerType->id,
        ]);

        $response = $this->putJson("/admin/surveys/{$survey->id}/questions/{$question->id}", [
            'text'           => '変更後の質問文',
            'answer_type_id' => $this->answerType->id,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 公開済みアンケートを下書きに戻すリクエストは拒否される(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);

        $response = $this->putJson("/admin/surveys/{$survey->id}", ['title' => $survey->title]);

        $response->assertStatus(422);
    }

    /** @test */
    public function タイトルが256文字の場合は作成できない(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/surveys', ['title' => str_repeat('a', 256)]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 質問文が501文字の場合は保存できない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/questions", [
            'text'           => str_repeat('あ', 501),
            'answer_type_id' => $this->answerType->id,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 見出しタイトルが101文字の場合は保存できない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->postJson("/admin/surveys/{$survey->id}/sections", [
            'title' => str_repeat('a', 101),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 公開→下書きの状態遷移はエラーになる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);

        // Try to update (which would be "draft" mode action)
        $response = $this->putJson("/admin/surveys/{$survey->id}", ['title' => $survey->title]);

        $response->assertStatus(422);
    }

    // ─── 異常系：権限違反 ────────────────────────────────────────────────

    /** @test */
    public function 他組織のアンケートにアクセスすると404になる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $orgB   = Organization::factory()->create();
        $survey = Survey::factory()->create(['organization_id' => $orgB->id]);

        $response = $this->putJson("/admin/surveys/{$survey->id}", ['title' => '不正な更新']);

        $response->assertStatus(404);
    }
}
