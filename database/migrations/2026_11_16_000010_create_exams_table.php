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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('duration')->default(60); // Durasi dalam menit
            $table->decimal('passing_grade', 5, 2)->default(75.00); // KKM
            $table->integer('total_questions')->default(0);
            $table->boolean('random_question')->default(false);
            $table->boolean('random_answer')->default(false);
            $table->string('token', 10)->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft, published, cancelled
            $table->boolean('is_results_published')->default(false);
            $table->timestamps();

            $table->index(['subject_id', 'status']);
            $table->index(['teacher_id', 'status']);
            $table->index(['start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
