<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('url'); // Основен URL на инструмента
            $table->string('documentation_url')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->string('logo_url')->nullable(); // Лого на инструмента
            $table->boolean('is_free')->default(false); // Безплатен ли е
            $table->decimal('price', 10, 2)->nullable(); // Цена ако е платен
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tools');
    }
};