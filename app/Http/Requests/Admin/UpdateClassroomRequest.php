<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAnyAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'max:20'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Kelas',
            'level' => 'Tingkat Kelas',
            'department_id' => 'Jurusan',
            'academic_year_id' => 'Tahun Ajaran',
            'is_active' => 'Status Aktif',
        ];
    }
}

