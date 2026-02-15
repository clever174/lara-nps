<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lesson_grades', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('rating'); // 1..5
            $table->string('lesson_title');
            $table->unsignedBigInteger('lesson_id');

            $table->string('course_title');
            $table->unsignedBigInteger('course_id');

            $table->text('comment')->nullable();

            // просто число, внешний ID
            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->unique(['lesson_id', 'user_id']);

            $table->index('lesson_id');
            $table->index('course_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_grades');
    }
};
