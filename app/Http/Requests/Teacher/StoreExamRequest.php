<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isTeacher() || $user?->isAnyAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', 'unique:exams,code'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration' => ['required', 'integer', 'min:1', 'max:1440'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'random_question' => ['nullable', 'boolean'],
            'random_answer' => ['nullable', 'boolean'],
            'token' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:draft,published,cancelled'],
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
            'code' => 'Kode Ujian',
            'title' => 'Judul Ujian',
            'description' => 'Deskripsi / Petunjuk Ujian',
            'subject_id' => 'Mata Pelajaran',
            'start_at' => 'Waktu Mulai Ujian',
            'end_at' => 'Waktu Selesai Ujian',
            'duration' => 'Durasi Ujian',
            'passing_grade' => 'Nilai KKM (Passing Grade)',
            'random_question' => 'Acak Urutan Soal',
            'random_answer' => 'Acak Urutan Opsi Jawaban',
            'token' => 'Token Ujian',
            'status' => 'Status Ujian',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_at.after' => 'Waktu selesai ujian harus lebih lambat dari waktu mulai ujian.',
            'duration.min' => 'Durasi ujian minimal 1 menit.',
        ];
    }
}
