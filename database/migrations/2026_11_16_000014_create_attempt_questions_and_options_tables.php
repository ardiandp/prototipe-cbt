<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->foreignId('selected_exam_question_option_id')->nullable()->constrained('exam_question_options')->nullOnDelete();
            $table->boolean('is_doubtful')->default(false);
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'exam_question_id']);
            $table->unique(['exam_attempt_id', 'order']);
            $table->index(['exam_attempt_id', 'order']);
        });

        Schema::create('exam_attempt_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_question_id')->constrained('exam_attempt_questions')->cascadeOnDelete();
            $table->foreignId('exam_question_option_id')->constrained('exam_question_options')->cascadeOnDelete();
            $table->string('display_label', 5); // 'A', 'B', 'C', 'D', 'E'
            $table->unsignedInteger('order');
            $table->timestamps();

            $table->unique(['exam_attempt_question_id', 'exam_question_option_id'], 'eaqo_question_option_unique');
            $table->unique(['exam_attempt_question_id', 'display_label'], 'eaqo_question_label_unique');
            $table->index(['exam_attempt_question_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_question_options');
        Schema::dropIfExists('exam_attempt_questions');
    }
};
