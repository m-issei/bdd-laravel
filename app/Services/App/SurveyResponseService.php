<?php

namespace App\Services\App;

use App\Models\Participant;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\Survey;

class SurveyResponseService
{
    public function save(Participant $participant, Survey $survey, array $answers): Response
    {
        $response = Response::firstOrCreate([
            'participant_id' => $participant->id,
            'survey_id'      => $survey->id,
        ]);

        foreach ($answers as $a) {
            ResponseAnswer::updateOrCreate(
                ['response_id' => $response->id, 'question_id' => $a['question_id']],
                ['value' => $a['value']]
            );
        }

        return $response;
    }

    public function submit(Participant $participant, Survey $survey, array $answers): Response
    {
        if (Response::where('participant_id', $participant->id)
            ->where('survey_id', $survey->id)
            ->whereNotNull('submitted_at')
            ->exists()) {
            abort(422, '既に提出済みです');
        }

        $questionCount = $survey->questions()->count();
        if (count($answers) < $questionCount) {
            abort(422, '全問回答が必要です');
        }

        $response = $this->save($participant, $survey, $answers);
        $response->update(['submitted_at' => now()]);

        return $response;
    }
}
