<?php

namespace Tests\Features\BDD\App;

use App\Models\AnswerType;
use App\Models\Organization;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: 参加者 — アンケート回答
 */
class SurveyResponseTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;
    private Participant $participant;
    private AnswerType $answerType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org         = Organization::factory()->create();
        $this->participant = Participant::factory()->create(['organization_id' => $this->org->id]);
        $this->answerType  = AnswerType::firstOrCreate(['name' => 'radio_1_5'], ['label' => '1〜5段階']);
    }

    // ─── 正常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 公開中のアンケートが一覧に表示される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);

        $response = $this->get('/app/surveys');

        $response->assertStatus(200);
        $response->assertSee($survey->title);
    }

    /** @test */
    public function 回答を途中保存するとsubmitted_atがnullのままresponseが作成される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        $response = $this->postJson("/app/surveys/{$survey->id}/save", [
            'answers' => [
                ['question_id' => $question->id, 'value' => '3'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('responses', [
            'participant_id' => $this->participant->id,
            'survey_id'      => $survey->id,
            'submitted_at'   => null,
        ]);
    }

    /** @test */
    public function 途中保存した回答は再アクセス時に復元される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        // Save first
        $resp = Response::create([
            'participant_id' => $this->participant->id,
            'survey_id'      => $survey->id,
        ]);
        ResponseAnswer::create([
            'response_id' => $resp->id,
            'question_id' => $question->id,
            'value'       => '4',
        ]);

        // Re-access the survey
        $response = $this->get("/app/surveys/{$survey->id}");

        $response->assertStatus(200);
        $response->assertSee('4');
    }

    /** @test */
    public function 全問回答して最終提出するとsubmitted_atに日時が記録される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        $response = $this->postJson("/app/surveys/{$survey->id}/submit", [
            'answers' => [
                ['question_id' => $question->id, 'value' => '5'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('responses', [
            'participant_id' => $this->participant->id,
            'survey_id'      => $survey->id,
            'submitted_at'   => null,
        ]);
        $dbResponse = Response::where('participant_id', $this->participant->id)
            ->where('survey_id', $survey->id)
            ->first();
        $this->assertNotNull($dbResponse->submitted_at);
    }

    /** @test */
    public function 複数のアンケートにそれぞれ回答できる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey1   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $survey2   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question1 = Question::factory()->create(['survey_id' => $survey1->id, 'answer_type_id' => $this->answerType->id]);
        $question2 = Question::factory()->create(['survey_id' => $survey2->id, 'answer_type_id' => $this->answerType->id]);

        $this->postJson("/app/surveys/{$survey1->id}/submit", [
            'answers' => [['question_id' => $question1->id, 'value' => '3']],
        ])->assertStatus(200);

        $this->postJson("/app/surveys/{$survey2->id}/submit", [
            'answers' => [['question_id' => $question2->id, 'value' => '4']],
        ])->assertStatus(200);

        $this->assertDatabaseCount('responses', 2);
    }

    /** @test */
    public function 下書きアンケートは参加者の一覧に表示されない(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey = Survey::factory()->create([
            'organization_id' => $this->org->id,
            'status'          => 'draft',
        ]);

        $response = $this->get('/app/surveys');

        $response->assertStatus(200);
        $response->assertDontSee($survey->title);
    }

    /** @test */
    public function 未回答アンケートは回答フォームが表示される(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        $response = $this->get("/app/surveys/{$survey->id}");

        $response->assertStatus(200);
        $response->assertSee('answer-form');
    }

    /** @test */
    public function 提出済みアンケートは閲覧のみで編集フォームは表示されない(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        $resp = Response::create([
            'participant_id' => $this->participant->id,
            'survey_id'      => $survey->id,
            'submitted_at'   => now(),
        ]);

        $response = $this->get("/app/surveys/{$survey->id}");

        $response->assertStatus(200);
        $response->assertSee('readonly-view');
        $response->assertDontSee('answer-form');
    }

    // ─── 異常系 ──────────────────────────────────────────────────────────

    /** @test */
    public function 最終提出済みアンケートに再提出しようとするとエラーになる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        // Submit once
        $this->postJson("/app/surveys/{$survey->id}/submit", [
            'answers' => [['question_id' => $question->id, 'value' => '3']],
        ])->assertStatus(200);

        // Try to submit again
        $response = $this->postJson("/app/surveys/{$survey->id}/submit", [
            'answers' => [['question_id' => $question->id, 'value' => '5']],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 全問回答せずに最終提出しようとするとエラーになる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $survey    = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question1 = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);
        $question2 = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        // Only answer 1 of 2 questions
        $response = $this->postJson("/app/surveys/{$survey->id}/submit", [
            'answers' => [['question_id' => $question1->id, 'value' => '3']],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function 他組織のアンケートに回答しようとすると404になる(): void
    {
        $this->actingAs($this->participant, 'participant');
        $orgB     = Organization::factory()->create();
        $survey   = Survey::factory()->published()->create(['organization_id' => $orgB->id]);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'answer_type_id' => $this->answerType->id]);

        $response = $this->postJson("/app/surveys/{$survey->id}/submit", [
            'answers' => [['question_id' => $question->id, 'value' => '3']],
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function 存在しないアンケートIDに回答しようとすると404になる(): void
    {
        $this->actingAs($this->participant, 'participant');

        $response = $this->postJson('/app/surveys/99999/submit', [
            'answers' => [],
        ]);

        $response->assertStatus(404);
    }
}
