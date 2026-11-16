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
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->boolean('can_retake')->default(false)->after('status');
            $table->text('retake_reason')->nullable()->after('can_retake');
            $table->foreignId('retake_authorized_by')->nullable()->after('retake_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('retake_authorized_at')->nullable()->after('retake_authorized_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dropForeign(['retake_authorized_by']);
            $table->dropColumn(['can_retake', 'retake_reason', 'retake_authorized_by', 'retake_authorized_at']);
        });
    }
};
