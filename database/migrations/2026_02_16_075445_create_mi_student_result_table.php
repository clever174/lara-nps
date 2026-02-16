<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mi_student_result', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->comment('ID студента (mi_student.id)');

            $table->unsignedInteger('attempt_number')
                ->comment('Номер попытки');

            $table->boolean('is_actual');

            // в SQL created_at NOT NULL DEFAULT CURRENT_TIMESTAMP
            // но в Laravel обычно timestamps. Тут оставим как у тебя:
            $table->timestamp('created_at')->useCurrent();

            $table->unsignedInteger('feedback_grade')
                ->comment('Оценка от ИИ');

            $table->text('prompt')->comment('Промпт от студента');
            $table->text('feedback_comment')->comment('Обратная связь от ИИ');
            $table->text('feedback_recommendation');

            $table->json('ai_json')
                ->nullable()
                ->comment('JSON от AI (тех. инфа)');

            $table->index('student_id');
            $table->index('attempt_number');

            // ВАЖНО: лучше добавить FK, раз это сущности связаны
            $table->foreign('student_id')
                ->references('id')->on('mi_student')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mi_student_result');
    }
};

