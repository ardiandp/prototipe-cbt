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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('status', 20)->default('in_progress')->index(); // in_progress, completed, expired, abandoned
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('total_correct')->nullable();
            $table->unsignedInteger('total_answered')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index(['exam_id', 'student_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
