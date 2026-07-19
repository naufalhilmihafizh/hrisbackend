<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();
        $dates = [
            '2026-05-25',
            '2026-05-26',
            '2026-05-27',
            '2026-05-28',
            '2026-05-29',
            '2026-05-30',
        ];

        // Jakarta coordinates base
        $baseLat = -6.2088;
        $baseLng = 106.8456;

        foreach ($employees as $employee) {
            foreach ($dates as $dateString) {
                // Random hour for check in (e.g. between 07:45 and 08:15)
                $minuteOffset = rand(-15, 15);
                $checkInTime = Carbon::parse("$dateString 08:00:00")->addMinutes($minuteOffset);
                $checkOutTime = Carbon::parse("$dateString 17:00:00")->addMinutes(rand(0, 30));

                $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                $status = $isLate ? 'late' : 'present';

                // Add slight randomness to GPS coordinates
                $latRandomness = (rand(-100, 100) / 100000);
                $lngRandomness = (rand(-100, 100) / 100000);

                Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date' => $dateString,
                    ],
                    [
                        'check_in_time' => $checkInTime,
                        'check_in_latitude' => $baseLat + $latRandomness,
                        'check_in_longitude' => $baseLng + $lngRandomness,
                        'check_out_time' => $checkOutTime,
                        'check_out_latitude' => $baseLat + $latRandomness,
                        'check_out_longitude' => $baseLng + $lngRandomness,
                        'status' => $status,
                        'notes' => $isLate ? 'Terlambat karena macet' : 'Tepat waktu',
                    ]
                );
            }
        }
    }
}
