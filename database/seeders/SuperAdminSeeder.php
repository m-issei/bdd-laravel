<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'super@example.com'],
            [
                'name'     => 'スーパー管理者',
                'password' => Hash::make('password'),
                'is_super' => true,
                'is_active' => true,
            ]
        );
    }
}
