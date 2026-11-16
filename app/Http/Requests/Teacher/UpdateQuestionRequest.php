<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:50'],
            'question_text' => ['required', 'string'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:draft,active,inactive'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
            'options.E' => ['required', 'string'],
            'correct_option' => ['required', 'in:A,B,C,D,E'],
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
            'code' => 'Kode Soal',
            'question_text' => 'Teks Pertanyaan',
            'difficulty' => 'Tingkat Kesulitan',
            'weight' => 'Bobot Nilai',
            'status' => 'Status Soal',
            'options.A' => 'Pilihan Jawaban A',
            'options.B' => 'Pilihan Jawaban B',
            'options.C' => 'Pilihan Jawaban C',
            'options.D' => 'Pilihan Jawaban D',
            'options.E' => 'Pilihan Jawaban E',
            'correct_option' => 'Kunci Jawaban Benar',
        ];
    }
}
