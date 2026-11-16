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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->integer('order')->default(1);
            $table->string('code', 50)->nullable();
            $table->longText('question_text');
            $table->string('question_image')->nullable();
            $table->string('difficulty', 20)->default('medium');
            $table->decimal('weight', 8, 2)->default(1.00);
            $table->timestamps();

            $table->index(['exam_id', 'order']);
        });

        Schema::create('exam_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->string('label', 10); // A, B, C, D, E
            $table->text('option_text');
            $table->string('option_image')->nullable();
            $table->boolean('is_correct')->default(false)->index();
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->unique(['exam_question_id', 'label']);
            $table->index(['exam_question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_options');
        Schema::dropIfExists('exam_questions');
    }
};

