<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobVacancy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CloseExpiredVacancies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacancies:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close job vacancies that have passed their deadline';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $vacancies = JobVacancy::where('status', 'OPEN')
            ->whereDate('closing_date', '<', $today)
            ->get();

        $count = 0;
        foreach ($vacancies as $vacancy) {
            $vacancy->update([
                'status' => 'CLOSED',
                'last_modified_by' => 'System (Auto-closed)',
                'last_modified_at' => now(),
            ]);
            $count++;
            Log::info("Closed expired vacancy: {$vacancy->position_title} (ID: {$vacancy->vacancy_id})");
        }

        $this->info("Successfully closed {$count} expired vacancies.");
        return 0;
    }
}
