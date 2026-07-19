<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'overtime_date',
    'duration_hours',
    'reason',
    'status',
    'approved_by',
    'approved_at',
    'rejection_reason'
])]
class Overtime extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'duration_hours' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
