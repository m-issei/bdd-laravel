<?php

namespace App\Services\Super;

use App\Models\Admin;

class AdminAccountService
{
    public function create(array $data): Admin
    {
        return Admin::create([
            'organization_id' => $data['organization_id'],
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => $data['password'],
            'is_super'        => false,
            'is_active'       => true,
        ]);
    }

    public function update(Admin $admin, array $data): Admin
    {
        $admin->update([
            'organization_id' => $data['organization_id'],
            'name'            => $data['name'],
            'email'           => $data['email'],
        ]);
        return $admin;
    }

    public function toggleActive(Admin $admin, bool $active): void
    {
        $admin->update(['is_active' => $active]);
    }

    public function delete(Admin $admin): void
    {
        $admin->delete();
    }
}
