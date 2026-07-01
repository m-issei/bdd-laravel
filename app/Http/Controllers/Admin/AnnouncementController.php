<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Admin\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementService $service) {}

    private function findAnnouncement(int $id): Announcement
    {
        return Announcement::where('id', $id)
            ->where('organization_id', Auth::guard('admin')->user()->organization_id)
            ->firstOrFail();
    }

    public function index(): View
    {
        $orgId         = Auth::guard('admin')->user()->organization_id;
        $announcements = Announcement::where('organization_id', $orgId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $orgId        = Auth::guard('admin')->user()->organization_id;
        $announcement = $this->service->create($orgId, $request->validated());

        return response()->json($announcement, 201);
    }

    public function update(UpdateAnnouncementRequest $request, int $id): JsonResponse
    {
        $announcement = $this->findAnnouncement($id);
        $this->service->update($announcement, $request->validated());

        return response()->json($announcement->fresh());
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $announcement = $this->findAnnouncement($id);
        $this->service->toggleStatus($announcement);

        return response()->json($announcement->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $announcement = $this->findAnnouncement($id);
        $this->service->delete($announcement);

        return response()->json(['message' => 'deleted']);
    }
}
