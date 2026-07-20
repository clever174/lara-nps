<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->comment('id студента GetCourse');
            $table->string('fio');
            $table->unsignedInteger('attempt_number');
            $table->string('file_path');
            $table->json('ai_json')->nullable()->comment('Полный ответ от нейросети');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_grades');
    }
};
