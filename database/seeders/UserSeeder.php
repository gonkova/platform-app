<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Вземи ID-тата на ролите
        $ownerRole = DB::table('roles')->where('name', 'owner')->first();
        $frontendRole = DB::table('roles')->where('name', 'frontend')->first();
        $backendRole = DB::table('roles')->where('name', 'backend')->first();

        // Провери дали ролите съществуват
        if (!$ownerRole || !$frontendRole || !$backendRole) {
            throw new \Exception('Ролите не са създадени. Първо изпълнете RoleSeeder.');
        }

        $users = [
            [
                'name' => 'Иван Иванов',
                'email' => 'ivan@admin.local',
                'password' => Hash::make('password'),
                'role_id' => $ownerRole->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Елена Петрова',
                'email' => 'elena@frontend.local',
                'password' => Hash::make('password'),
                'role_id' => $frontendRole->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Петър Георгиев',
                'email' => 'petar@backend.local',
                'password' => Hash::make('password'),
                'role_id' => $backendRole->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}