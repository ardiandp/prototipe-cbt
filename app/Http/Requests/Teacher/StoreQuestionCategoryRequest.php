<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() || $this->user()?->isAnyAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:question_categories,code'],
            'name' => ['required', 'string', 'max:150'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'level' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'code' => 'Kode Bank Soal',
            'name' => 'Nama Bank Soal',
            'subject_id' => 'Mata Pelajaran',
            'level' => 'Tingkat Kelas',
            'description' => 'Deskripsi',
            'is_active' => 'Status Aktif',
        ];
    }
}
