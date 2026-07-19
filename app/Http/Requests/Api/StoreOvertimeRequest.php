<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'overtime_date' => ['required', 'date', 'date_format:Y-m-d'],
            'duration_hours' => ['required', 'numeric', 'gt:0', 'max:8'], // limit overtime to max 8 hours per request
            'reason' => ['required', 'string', 'min:5'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'overtime_date.required' => 'Tanggal lembur wajib diisi.',
            'overtime_date.date' => 'Format tanggal lembur tidak valid.',
            'overtime_date.date_format' => 'Format tanggal lembur harus YYYY-MM-DD.',
            'duration_hours.required' => 'Durasi lembur wajib diisi.',
            'duration_hours.numeric' => 'Durasi lembur harus berupa angka.',
            'duration_hours.gt' => 'Durasi lembur harus lebih dari 0 jam.',
            'duration_hours.max' => 'Durasi lembur maksimal 8 jam per hari.',
            'reason.required' => 'Alasan lembur wajib diisi.',
            'reason.min' => 'Alasan lembur minimal 5 karakter.',
        ];
    }
}
