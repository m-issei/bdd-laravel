<?php

namespace App\Services\Admin;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;

class AnnouncementService
{
    public function create(int $organizationId, array $data): Announcement
    {
        return Announcement::create([
            'organization_id' => $organizationId,
            'title'           => $data['title'],
            'body'            => $data['body'],
            'status'          => AnnouncementStatus::Draft,
        ]);
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update([
            'title' => $data['title'],
            'body'  => $data['body'],
        ]);

        return $announcement;
    }

    public function toggleStatus(Announcement $announcement): Announcement
    {
        $newStatus = $announcement->status === AnnouncementStatus::Published
            ? AnnouncementStatus::Draft
            : AnnouncementStatus::Published;

        $announcement->update(['status' => $newStatus]);

        return $announcement;
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }
}
