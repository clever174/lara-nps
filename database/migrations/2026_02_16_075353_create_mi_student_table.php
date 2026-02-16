<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mi_student', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT

            $table->unsignedInteger('user_id')
                ->comment('id студента GetCourse');

            $table->string('fio');

            $table->json('general_feedback')
                ->nullable()
                ->comment('Общая обратная связь от ИИ');

            $table->json('ai_json')->nullable();

            // Эквивалент твоих timestamp DEFAULT CURRENT_TIMESTAMP / ON UPDATE
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mi_student');
    }
};

