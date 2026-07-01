<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    private function findAnnouncement(int $id): Announcement
    {
        $participant = Auth::guard('participant')->user();

        return Announcement::where('id', $id)
            ->where('organization_id', $participant->organization_id)
            ->where('status', 'published')
            ->firstOrFail();
    }

    public function index(): View
    {
        $participant   = Auth::guard('participant')->user();
        $announcements = Announcement::where('organization_id', $participant->organization_id)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('app.announcements.index', compact('announcements'));
    }

    public function show(int $id): View
    {
        $announcement = $this->findAnnouncement($id);

        return view('app.announcements.show', compact('announcement'));
    }
}
