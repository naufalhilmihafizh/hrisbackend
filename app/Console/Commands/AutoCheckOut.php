<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCheckOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically check out employees who forgot to check out today.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        // Find all attendances today where check_out_time is null
        $attendances = Attendance::where('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            // Set check_out_time to 17:00:00
            $defaultCheckOutTime = Carbon::createFromFormat('H:i:s', config('hris.work_end_time', '17:00:00'));
            $defaultCheckOutTime->setDate(Carbon::parse($today)->year, Carbon::parse($today)->month, Carbon::parse($today)->day);

            $attendance->update([
                'check_out_time' => $defaultCheckOutTime,
                'notes' => trim($attendance->notes . ' (Auto Check-Out)')
            ]);
            $count++;
        }

        $this->info("Auto check-out completed. Processed {$count} records.");
        Log::info("Auto check-out completed. Processed {$count} records.");
    }
}
