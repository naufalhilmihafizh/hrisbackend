<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceResource extends JsonResource
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
            'date' => $this->date ? Carbon::parse($this->date)->format('Y-m-d') : null,
            'check_in_time' => $this->check_in_time ? Carbon::parse($this->check_in_time)->toIso8601String() : null,
            'check_in_latitude' => $this->check_in_latitude ? (float) $this->check_in_latitude : null,
            'check_in_longitude' => $this->check_in_longitude ? (float) $this->check_in_longitude : null,
            'check_out_time' => $this->check_out_time ? Carbon::parse($this->check_out_time)->toIso8601String() : null,
            'check_out_latitude' => $this->check_out_latitude ? (float) $this->check_out_latitude : null,
            'check_out_longitude' => $this->check_out_longitude ? (float) $this->check_out_longitude : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
