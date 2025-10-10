<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'AI Writing', 'slug' => 'ai-writing', 'description' => 'AI инструменти за писане и съдържание'],
            ['name' => 'AI Design', 'slug' => 'ai-design', 'description' => 'AI инструменти за дизайн и визуализация'],
            ['name' => 'AI Coding', 'slug' => 'ai-coding', 'description' => 'AI инструменти за програмиране'],
            ['name' => 'AI Analytics', 'slug' => 'ai-analytics', 'description' => 'AI инструменти за анализ на данни'],
            ['name' => 'AI Marketing', 'slug' => 'ai-marketing', 'description' => 'AI инструменти за маркетинг'],
            ['name' => 'AI Productivity', 'slug' => 'ai-productivity', 'description' => 'AI инструменти за продуктивност'],
            ['name' => 'AI Image Generation', 'slug' => 'ai-image-generation', 'description' => 'AI генериране на изображения'],
            ['name' => 'AI Video', 'slug' => 'ai-video', 'description' => 'AI инструменти за видео'],
            ['name' => 'AI Audio', 'slug' => 'ai-audio', 'description' => 'AI инструменти за аудио'],
            ['name' => 'AI Chat', 'slug' => 'ai-chat', 'description' => 'AI чат асистенти'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}