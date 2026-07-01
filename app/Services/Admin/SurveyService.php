<?php

namespace App\Services\Admin;

use App\Enums\SurveyStatus;
use App\Models\AnswerType;
use App\Models\Question;
use App\Models\Survey;
use App\Models\SurveySection;

class SurveyService
{
    public function create(int $organizationId, array $data): Survey
    {
        return Survey::create([
            'organization_id' => $organizationId,
            'title'           => $data['title'],
            'status'          => SurveyStatus::Draft,
        ]);
    }

    public function update(Survey $survey, array $data): Survey
    {
        if ($survey->status === SurveyStatus::Published) {
            abort(422, '公開済みのアンケートは編集できません');
        }

        $survey->update(['title' => $data['title']]);

        return $survey;
    }

    public function publish(Survey $survey): void
    {
        if ($survey->status === SurveyStatus::Published) {
            abort(422, 'すでに公開済みです');
        }

        if ($survey->questions()->count() === 0) {
            abort(422, '質問が必要です');
        }

        $survey->update(['status' => SurveyStatus::Published]);
    }

    public function delete(Survey $survey): void
    {
        $survey->delete();
    }

    public function createSection(Survey $survey, array $data): SurveySection
    {
        // Check unique title within survey
        $exists = SurveySection::where('survey_id', $survey->id)
            ->where('title', $data['title'])
            ->exists();

        if ($exists) {
            abort(422, '同じタイトルの見出しが既に存在します');
        }

        $order = SurveySection::where('survey_id', $survey->id)->max('order') ?? 0;

        return SurveySection::create([
            'survey_id' => $survey->id,
            'title'     => $data['title'],
            'order'     => $order + 1,
        ]);
    }

    public function deleteSection(SurveySection $section): void
    {
        // Detach questions from section (set section_id to null)
        Question::where('survey_section_id', $section->id)
            ->update(['survey_section_id' => null]);

        $section->delete();
    }

    public function createQuestion(Survey $survey, array $data): Question
    {
        if ($survey->status === SurveyStatus::Published) {
            abort(422, '公開済みのアンケートの質問は編集できません');
        }

        $order = Question::where('survey_id', $survey->id)->max('order') ?? 0;

        return Question::create([
            'survey_id'         => $survey->id,
            'survey_section_id' => $data['survey_section_id'] ?? null,
            'answer_type_id'    => $data['answer_type_id'],
            'text'              => $data['text'],
            'order'             => $data['order'] ?? $order + 1,
        ]);
    }

    public function updateQuestion(Question $question, array $data): Question
    {
        $survey = $question->survey;

        if ($survey->status === SurveyStatus::Published) {
            abort(422, '公開済みのアンケートの質問は編集できません');
        }

        $question->update([
            'text'              => $data['text'],
            'answer_type_id'    => $data['answer_type_id'],
            'survey_section_id' => $data['survey_section_id'] ?? $question->survey_section_id,
            'order'             => $data['order'] ?? $question->order,
        ]);

        return $question;
    }

    public function reorder(Survey $survey, array $orders): void
    {
        foreach ($orders as $item) {
            Question::where('id', $item['id'])
                ->where('survey_id', $survey->id)
                ->update(['order' => $item['order']]);
        }
    }
}
