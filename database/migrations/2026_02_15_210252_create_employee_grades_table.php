<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_grades', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment

            $table->unsignedTinyInteger('rating');

            $table->string('employee_name');
            $table->unsignedTinyInteger('employee_type');

            $table->unsignedBigInteger('user_id');

            $table->text('comment');

            $table->timestamp('created_at');

            $table->string('course_title');
            $table->string('lesson_id');

            // Уникальный индекс
            $table->unique(
                ['user_id', 'employee_name', 'lesson_id', 'employee_type'],
                'user_employee_lesson_type_unique'
            );

            // Обычные индексы
            $table->index('employee_name');
            $table->index('employee_type');
            $table->index('lesson_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_grades');
    }
};
