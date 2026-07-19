<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
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
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'maternity', 'other'])],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5'],
            'attachment' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'leave_type.required' => 'Tipe cuti wajib dipilih.',
            'leave_type.in' => 'Tipe cuti tidak valid.',
            'start_date.required' => 'Tanggal mulai cuti wajib diisi.',
            'start_date.date' => 'Format tanggal mulai cuti tidak valid.',
            'start_date.date_format' => 'Format tanggal mulai cuti harus YYYY-MM-DD.',
            'end_date.required' => 'Tanggal selesai cuti wajib diisi.',
            'end_date.date' => 'Format tanggal selesai cuti tidak valid.',
            'end_date.date_format' => 'Format tanggal selesai cuti harus YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal selesai cuti harus sama atau setelah tanggal mulai.',
            'reason.required' => 'Alasan cuti wajib diisi.',
            'reason.min' => 'Alasan cuti minimal 5 karakter.',
            'attachment.required' => 'Bukti foto wajib diunggah.',
            'attachment.file' => 'Bukti foto harus berupa file.',
            'attachment.image' => 'Bukti foto harus berupa gambar.',
            'attachment.mimes' => 'Bukti foto harus berformat jpeg, png, jpg, atau gif.',
            'attachment.max' => 'Ukuran bukti foto maksimal 5MB.',
        ];
    }
}
