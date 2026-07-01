<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\SaveResponseRequest;
use App\Http\Requests\App\SubmitResponseRequest;
use App\Models\Response;
use App\Models\Survey;
use App\Services\App\SurveyResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurveyResponseController extends Controller
{
    public function __construct(private readonly SurveyResponseService $service) {}

    private function findSurvey(int $id): Survey
    {
        $participant = Auth::guard('participant')->user();

        return Survey::where('id', $id)
            ->where('organization_id', $participant->organization_id)
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    public function index(): View
    {
        $participant = Auth::guard('participant')->user();
        $surveys     = Survey::where('organization_id', $participant->organization_id)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('app.surveys.index', compact('surveys'));
    }

    public function show(int $id): View
    {
        $participant = Auth::guard('participant')->user();
        $survey      = $this->findSurvey($id);
        $response    = Response::where('participant_id', $participant->id)
            ->where('survey_id', $survey->id)
            ->first();

        return view('app.surveys.show', compact('survey', 'response'));
    }

    public function save(SaveResponseRequest $request, int $id): JsonResponse
    {
        $participant = Auth::guard('participant')->user();
        $survey      = $this->findSurvey($id);
        $response    = $this->service->save($participant, $survey, $request->validated()['answers'] ?? []);

        return response()->json($response);
    }

    public function submit(SubmitResponseRequest $request, int $id): JsonResponse
    {
        $participant = Auth::guard('participant')->user();
        $survey      = $this->findSurvey($id);
        $response    = $this->service->submit($participant, $survey, $request->validated()['answers'] ?? []);

        return response()->json($response);
    }
}
