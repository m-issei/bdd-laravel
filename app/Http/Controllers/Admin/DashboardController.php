<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\Admin\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function index(): View
    {
        return view('admin.dashboard');
    }

    public function survey(int $id): JsonResponse
    {
        $orgId  = Auth::guard('admin')->user()->organization_id;
        $survey = Survey::where('id', $id)
            ->where('organization_id', $orgId)
            ->firstOrFail();

        $stats = $this->service->getStats($survey);

        return response()->json($stats);
    }
}
