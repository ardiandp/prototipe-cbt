<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinalizeExpiredAttemptsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cbt:finalize-expired-attempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize all in-progress exam attempts that have passed their expires_at deadline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $expiredAttempts = ExamAttempt::where('status', 'in_progress')
            ->where('expires_at', '<=', $now)
            ->get();

        $count = $expiredAttempts->count();

        foreach ($expiredAttempts as $attempt) {
            $attempt->markAsExpired();
        }

        $this->info("Successfully finalized {$count} expired exam attempt(s).");

        return Command::SUCCESS;
    }
}
