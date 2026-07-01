<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\StoreSurveyRequest;
use App\Http\Requests\Admin\StoreSurveySectionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Http\Requests\Admin\UpdateSurveyRequest;
use App\Models\Question;
use App\Models\Survey;
use App\Models\SurveySection;
use App\Services\Admin\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function __construct(private readonly SurveyService $service) {}

    private function findSurvey(int $id): Survey
    {
        return Survey::where('id', $id)
            ->where('organization_id', Auth::guard('admin')->user()->organization_id)
            ->firstOrFail();
    }

    private function findQuestion(int $surveyId, int $questionId): Question
    {
        $survey = $this->findSurvey($surveyId);
        return Question::where('id', $questionId)
            ->where('survey_id', $survey->id)
            ->firstOrFail();
    }

    public function index(): View
    {
        $orgId   = Auth::guard('admin')->user()->organization_id;
        $surveys = Survey::withTrashed()
            ->where('organization_id', $orgId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.surveys.index', compact('surveys'));
    }

    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $orgId  = Auth::guard('admin')->user()->organization_id;
        $survey = $this->service->create($orgId, $request->validated());

        return response()->json($survey, 201);
    }

    public function update(UpdateSurveyRequest $request, int $id): JsonResponse
    {
        $survey = $this->findSurvey($id);
        $this->service->update($survey, $request->validated());

        return response()->json($survey->fresh());
    }

    public function publish(int $id): JsonResponse
    {
        $survey = $this->findSurvey($id);
        $this->service->publish($survey);

        return response()->json($survey->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $survey = $this->findSurvey($id);
        $this->service->delete($survey);

        return response()->json(['message' => 'deleted']);
    }

    public function storeSection(StoreSurveySectionRequest $request, int $id): JsonResponse
    {
        $survey  = $this->findSurvey($id);
        $section = $this->service->createSection($survey, $request->validated());

        return response()->json($section, 201);
    }

    public function destroySection(int $survey, int $section): JsonResponse
    {
        $surveyModel  = $this->findSurvey($survey);
        $sectionModel = SurveySection::where('id', $section)
            ->where('survey_id', $surveyModel->id)
            ->firstOrFail();

        $this->service->deleteSection($sectionModel);

        return response()->json(['message' => 'deleted']);
    }

    public function storeQuestion(StoreQuestionRequest $request, int $id): JsonResponse
    {
        $survey   = $this->findSurvey($id);
        $question = $this->service->createQuestion($survey, $request->validated());

        return response()->json($question, 201);
    }

    public function updateQuestion(UpdateQuestionRequest $request, int $survey, int $question): JsonResponse
    {
        $q = $this->findQuestion($survey, $question);
        $this->service->updateQuestion($q, $request->validated());

        return response()->json($q->fresh());
    }

    public function destroyQuestion(int $survey, int $question): JsonResponse
    {
        $q = $this->findQuestion($survey, $question);
        $q->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function reorder(Request $request, int $id): JsonResponse
    {
        $survey = $this->findSurvey($id);
        $orders = $request->validate(['orders' => ['required', 'array']]);
        $this->service->reorder($survey, $orders['orders']);

        return response()->json(['message' => 'reordered']);
    }
}
