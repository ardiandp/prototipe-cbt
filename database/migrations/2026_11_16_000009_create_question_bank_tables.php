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
        // 1. Kategori / Bank Soal
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('level', 50)->nullable(); // e.g. "Semua", "10", "11", "12"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['subject_id', 'is_active']);
        });

        // 2. Butir Soal (Questions)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_category_id')->constrained('question_categories')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('code', 50)->nullable();
            $table->longText('question_text');
            $table->string('question_image')->nullable();
            $table->string('difficulty', 20)->default('medium')->index(); // easy, medium, hard
            $table->decimal('weight', 8, 2)->default(1.00); // bobot nilai positif
            $table->string('status', 20)->default('active')->index(); // draft, active, inactive
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['question_category_id', 'status']);
        });

        // 3. Pilihan Jawaban (Question Options: A, B, C, D, E)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('label', 10); // 'A', 'B', 'C', 'D', 'E'
            $table->text('option_text');
            $table->string('option_image')->nullable();
            $table->boolean('is_correct')->default(false)->index();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['question_id', 'label']);
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_categories');
    }
};
