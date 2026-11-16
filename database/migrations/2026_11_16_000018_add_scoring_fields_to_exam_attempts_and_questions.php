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
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->unsignedInteger('total_incorrect')->nullable()->after('total_answered');
            $table->unsignedInteger('total_unanswered')->nullable()->after('total_incorrect');
            $table->decimal('total_weight', 8, 2)->nullable()->after('total_unanswered');
            $table->decimal('earned_weight', 8, 2)->nullable()->after('total_weight');
            $table->boolean('is_passed')->nullable()->after('earned_weight');
        });

        Schema::table('exam_attempt_questions', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->after('is_doubtful');
            $table->decimal('earned_weight', 8, 2)->default(0)->after('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'total_incorrect',
                'total_unanswered',
                'total_weight',
                'earned_weight',
                'is_passed',
            ]);
        });

        Schema::table('exam_attempt_questions', function (Blueprint $table) {
            $table->dropColumn(['is_correct', 'earned_weight']);
        });
    }
};

