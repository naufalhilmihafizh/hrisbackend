<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'period_month' => $this->period_month,
            'period_year' => $this->period_year,
            'base_salary' => (float) $this->base_salary,
            'overtime_pay' => (float) $this->overtime_pay,
            'overtime_hours' => (float) $this->overtime_hours,
            'deductions' => (float) $this->deductions,
            'deduction_details' => $this->deduction_details,
            'total_salary' => (float) $this->total_salary,
            'status' => $this->status,
            'paid_at' => $this->paid_at ? Carbon::parse($this->paid_at)->toIso8601String() : null,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toIso8601String() : null,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'position' => $this->user->position ? $this->user->position->name : null,
                    'department' => $this->user->department ? $this->user->department->name : null,
                ];
            }),
        ];
    }
}
