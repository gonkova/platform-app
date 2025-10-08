<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Text Generation',
                'slug' => 'text-generation',
                'description' => 'AI инструменти за генериране на текст и съдържание',
                'icon' => '✍️',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Image Generation',
                'slug' => 'image-generation',
                'description' => 'AI инструменти за създаване и редактиране на изображения',
                'icon' => '🎨',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Code Assistant',
                'slug' => 'code-assistant',
                'description' => 'AI асистенти за програмиране и разработка',
                'icon' => '💻',
                'color' => '#10B981',
            ],
            [
                'name' => 'Data Analysis',
                'slug' => 'data-analysis',
                'description' => 'Инструменти за анализ и визуализация на данни',
                'icon' => '📊',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Video & Audio',
                'slug' => 'video-audio',
                'description' => 'AI за обработка на видео и аудио съдържание',
                'icon' => '🎬',
                'color' => '#EF4444',
            ],
            [
                'name' => 'Productivity',
                'slug' => 'productivity',
                'description' => 'Инструменти за повишаване на продуктивността',
                'icon' => '⚡',
                'color' => '#6366F1',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}