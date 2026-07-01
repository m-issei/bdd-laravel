<?php

namespace App\Services\Admin;

use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Survey;

class DashboardService
{
    public function getStats(Survey $survey): array
    {
        $submittedResponses = Response::where('survey_id', $survey->id)
            ->whereNotNull('submitted_at')
            ->get();

        $responseCount = $submittedResponses->count();

        $questions = $survey->questions()->get();
        $questionStats = [];

        foreach ($questions as $question) {
            $avg = ResponseAnswer::whereIn('response_id', $submittedResponses->pluck('id'))
                ->where('question_id', $question->id)
                ->avg('value');

            $questionStats[] = [
                'question_id' => $question->id,
                'text'        => $question->text,
                'average'     => $avg,
            ];
        }

        return [
            'response_count' => $responseCount,
            'questions'      => $questionStats,
        ];
    }
}
