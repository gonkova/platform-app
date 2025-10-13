<?php

namespace Database\Seeders;

use App\Models\AiTool;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiToolSeeder extends Seeder
{
    public function run(): void
    {
        // Вземи Owner потребителя като creator
        $owner = User::where('email', 'ivan@admin.local')->first();
        
        // Вземи категориите (използвай правилните slugs от CategorySeeder)
        $textGen = Category::where('slug', 'ai-writing')->first();
        $imageGen = Category::where('slug', 'ai-image-generation')->first();
        $codeAssist = Category::where('slug', 'ai-coding')->first();
        $productivity = Category::where('slug', 'ai-productivity')->first();
        
        // Вземи ролите
        $ownerRole = Role::where('name', 'owner')->first();
        $frontendRole = Role::where('name', 'frontend')->first();
        $backendRole = Role::where('name', 'backend')->first();

        // ChatGPT
        $chatgpt = AiTool::create([
            'name' => 'ChatGPT',
            'slug' => 'chatgpt',
            'description' => 'Мощен AI чатбот от OpenAI за генериране на текст, код и отговори на въпроси',
            'url' => 'https://chat.openai.com',
            'documentation_url' => 'https://platform.openai.com/docs',
            'difficulty_level' => 'beginner',
            'is_free' => true,
            'is_active' => true,
            'created_by' => $owner->id,
        ]);
        $chatgpt->categories()->attach([$textGen->id, $codeAssist->id, $productivity->id]);
        $chatgpt->roles()->attach([$ownerRole->id, $frontendRole->id, $backendRole->id]);

        // GitHub Copilot
        $copilot = AiTool::create([
            'name' => 'GitHub Copilot',
            'slug' => 'github-copilot',
            'description' => 'AI програмен асистент, който помага при писане на код директно в редактора',
            'url' => 'https://github.com/features/copilot',
            'documentation_url' => 'https://docs.github.com/en/copilot',
            'difficulty_level' => 'intermediate',
            'is_free' => false,
            'price' => 10.00,
            'is_active' => true,
            'created_by' => $owner->id,
        ]);
        $copilot->categories()->attach([$codeAssist->id]);
        $copilot->roles()->attach([$frontendRole->id, $backendRole->id]);

        // Midjourney
        $midjourney = AiTool::create([
            'name' => 'Midjourney',
            'slug' => 'midjourney',
            'description' => 'AI генератор на изображения с висока детайлност и артистичен стил',
            'url' => 'https://www.midjourney.com',
            'documentation_url' => 'https://docs.midjourney.com',
            'difficulty_level' => 'intermediate',
            'is_free' => false,
            'price' => 30.00,
            'is_active' => true,
            'created_by' => $owner->id,
        ]);
        $midjourney->categories()->attach([$imageGen->id]);
        $midjourney->roles()->attach([$frontendRole->id, $ownerRole->id]);

        // Claude
        $claude = AiTool::create([
            'name' => 'Claude',
            'slug' => 'claude',
            'description' => 'AI асистент от Anthropic с фокус върху безопасност и полезност',
            'url' => 'https://claude.ai',
            'documentation_url' => 'https://docs.anthropic.com',
            'difficulty_level' => 'beginner',
            'is_free' => true,
            'is_active' => true,
            'created_by' => $owner->id,
        ]);
        $claude->categories()->attach([$textGen->id, $codeAssist->id, $productivity->id]);
        $claude->roles()->attach([$ownerRole->id, $frontendRole->id, $backendRole->id]);

        // Notion AI
        $notion = AiTool::create([
            'name' => 'Notion AI',
            'slug' => 'notion-ai',
            'description' => 'AI интеграция в Notion за писане, обобщаване и организиране на информация',
            'url' => 'https://www.notion.so/product/ai',
            'difficulty_level' => 'beginner',
            'is_free' => false,
            'price' => 10.00,
            'is_active' => true,
            'created_by' => $owner->id,
        ]);
        $notion->categories()->attach([$textGen->id, $productivity->id]);
        $notion->roles()->attach([$ownerRole->id, $frontendRole->id, $backendRole->id]);
    }
}