<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'owner',
                'display_name' => 'Owner',
                'description' => 'Пълен достъп до всички функционалности на платформата',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'frontend',
                'display_name' => 'Frontend Developer',
                'description' => 'Достъп до frontend задачи и ресурси',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'backend',
                'display_name' => 'Backend Developer',
                'description' => 'Достъп до backend задачи и ресурси',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
