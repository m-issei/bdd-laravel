<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreParticipantRequest;
use App\Http\Requests\Admin\UpdateParticipantRequest;
use App\Models\Participant;
use App\Services\Admin\ParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function __construct(private readonly ParticipantService $service) {}

    private function findParticipant(int $id): Participant
    {
        return Participant::withTrashed()
            ->where('id', $id)
            ->where('organization_id', Auth::guard('admin')->user()->organization_id)
            ->firstOrFail();
    }

    public function index(): View
    {
        $orgId        = Auth::guard('admin')->user()->organization_id;
        $participants = Participant::withTrashed()
            ->where('organization_id', $orgId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.participants.index', compact('participants'));
    }

    public function store(StoreParticipantRequest $request): JsonResponse
    {
        $orgId       = Auth::guard('admin')->user()->organization_id;
        $participant = $this->service->create($orgId, $request->validated());

        return response()->json($participant, 201);
    }

    public function update(UpdateParticipantRequest $request, int $id): JsonResponse
    {
        $participant = $this->findParticipant($id);
        $this->service->update($participant, $request->validated());

        return response()->json($participant->fresh());
    }

    public function toggleActive(int $id): JsonResponse
    {
        $participant = $this->findParticipant($id);
        $this->service->toggleActive($participant);

        return response()->json($participant->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $participant = $this->findParticipant($id);
        $this->service->delete($participant);

        return response()->json(['message' => 'deleted']);
    }
}
