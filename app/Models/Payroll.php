<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'period_month',
    'period_year',
    'base_salary',
    'overtime_pay',
    'overtime_hours',
    'deductions',
    'deduction_details',
    'total_salary',
    'status',
    'paid_at',
    'notes'
])]
class Payroll extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'base_salary' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'deductions' => 'decimal:2',
            'deduction_details' => 'array',
            'total_salary' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
