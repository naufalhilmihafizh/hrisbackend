<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HRIS Configuration
    |--------------------------------------------------------------------------
    |
    | Standard values for working hours, overtime calculation divisors, and
    | absence deduction rate calculations.
    |
    */

    // Time when the workday starts. Check-in after this time is marked as late.
    'work_start_time' => '09:00:00',

    // Standard monthly working hours divisor used for overtime pay calculation (Indonesian Labor Law).
    'overtime_divisor' => 173,

    // Overtime hourly rate multiplier (Indonesian Labor Law).
    'overtime_rate_multiplier' => 1.5,

    // Deduction rate of base salary per day of absence (unexcused absence).
    // Defaults to 1/22 (approx 0.04545) assuming 22 working days in a month.
    'absence_deduction_rate' => 1 / 22,
];
