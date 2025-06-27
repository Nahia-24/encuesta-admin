<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')
                  ->constrained('survey_responses')->onDelete('cascade');
            $table->foreignId('survey_question_id')
                  ->constrained('survey_questions')->onDelete('cascade');
            $table->text('answer'); // texto libre o IDs/JSON para opciones
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
