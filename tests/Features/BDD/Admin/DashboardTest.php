<?php

namespace Tests\Features\BDD\Admin;

use App\Models\Admin;
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
 * Feature: 管理者 — ダッシュボード
 */
class DashboardTest extends TestCase
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
    public function アンケートの回答人数を正しく集計できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);

        // Create 3 submitted responses
        for ($i = 0; $i < 3; $i++) {
            $participant = Participant::factory()->create(['organization_id' => $this->org->id]);
            Response::factory()->submitted()->create([
                'participant_id' => $participant->id,
                'survey_id'      => $survey->id,
            ]);
        }

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJson(['response_count' => 3]);
    }

    /** @test */
    public function 質問ごとの回答平均値を正しく集計できる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create([
            'survey_id'      => $survey->id,
            'answer_type_id' => $this->answerType->id,
        ]);

        // Create 2 submitted responses with values 3 and 5 (avg = 4)
        foreach ([3, 5] as $value) {
            $participant = Participant::factory()->create(['organization_id' => $this->org->id]);
            $resp        = Response::factory()->submitted()->create([
                'participant_id' => $participant->id,
                'survey_id'      => $survey->id,
            ]);
            ResponseAnswer::create([
                'response_id' => $resp->id,
                'question_id' => $question->id,
                'value'       => $value,
            ]);
        }

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(4.0, $data['questions'][0]['average']);
    }

    /** @test */
    public function 回答が0件の場合は人数0・平均値nullが返る(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey   = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $question = Question::factory()->create([
            'survey_id'      => $survey->id,
            'answer_type_id' => $this->answerType->id,
        ]);

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJson(['response_count' => 0]);
        $data = $response->json();
        $this->assertNull($data['questions'][0]['average']);
    }

    /** @test */
    public function 途中保存の回答は集計に含まれない(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey      = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $participant = Participant::factory()->create(['organization_id' => $this->org->id]);

        // Unsubmitted response
        Response::factory()->create([
            'participant_id' => $participant->id,
            'survey_id'      => $survey->id,
            'submitted_at'   => null,
        ]);

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJson(['response_count' => 0]);
    }

    /** @test */
    public function 提出済みの回答のみ集計に含まれる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey = Survey::factory()->published()->create(['organization_id' => $this->org->id]);

        // 1 submitted, 1 not submitted
        $p1 = Participant::factory()->create(['organization_id' => $this->org->id]);
        Response::factory()->submitted()->create(['participant_id' => $p1->id, 'survey_id' => $survey->id]);

        $p2 = Participant::factory()->create(['organization_id' => $this->org->id]);
        Response::factory()->create(['participant_id' => $p2->id, 'survey_id' => $survey->id, 'submitted_at' => null]);

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJson(['response_count' => 1]);
    }

    /** @test */
    public function 論理削除済み参加者の回答も集計に含まれる(): void
    {
        $this->actingAs($this->admin, 'admin');
        $survey      = Survey::factory()->published()->create(['organization_id' => $this->org->id]);
        $participant = Participant::factory()->create(['organization_id' => $this->org->id]);

        Response::factory()->submitted()->create([
            'participant_id' => $participant->id,
            'survey_id'      => $survey->id,
        ]);

        // Soft-delete the participant
        $participant->delete();

        $response = $this->getJson("/admin/surveys/{$survey->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJson(['response_count' => 1]);
    }
}
