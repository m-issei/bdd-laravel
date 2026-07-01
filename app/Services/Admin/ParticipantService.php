<?php

namespace App\Services\Admin;

use App\Models\Participant;
use Illuminate\Support\Facades\Hash;

class ParticipantService
{
    public function create(int $organizationId, array $data): Participant
    {
        return Participant::create([
            'organization_id' => $organizationId,
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'is_active'       => true,
        ]);
    }

    public function update(Participant $participant, array $data): Participant
    {
        $participant->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        return $participant;
    }

    public function delete(Participant $participant): void
    {
        $participant->delete();
    }

    public function toggleActive(Participant $participant): Participant
    {
        $participant->update(['is_active' => !$participant->is_active]);

        return $participant;
    }
}
