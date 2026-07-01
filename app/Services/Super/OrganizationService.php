<?php

namespace App\Services\Super;

use App\Models\Organization;

class OrganizationService
{
    public function create(array $data): Organization
    {
        return Organization::create($data);
    }

    public function update(Organization $org, array $data): Organization
    {
        $org->update($data);
        return $org;
    }

    public function delete(Organization $org): void
    {
        $org->delete();
    }
}
