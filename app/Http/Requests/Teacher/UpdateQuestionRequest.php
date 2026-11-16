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
            'question_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'remove_question_image' => ['nullable', 'boolean'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:draft,active,inactive'],
            'options' => ['required', 'array'],
            'options.A' => ['required', 'string'],
            'options.B' => ['required', 'string'],
            'options.C' => ['required', 'string'],
            'options.D' => ['required', 'string'],
            'options.E' => ['required', 'string'],
            'option_images' => ['nullable', 'array'],
            'option_images.A' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'option_images.B' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'option_images.C' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'option_images.D' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'option_images.E' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'remove_option_images' => ['nullable', 'array'],
            'remove_option_images.A' => ['nullable', 'boolean'],
            'remove_option_images.B' => ['nullable', 'boolean'],
            'remove_option_images.C' => ['nullable', 'boolean'],
            'remove_option_images.D' => ['nullable', 'boolean'],
            'remove_option_images.E' => ['nullable', 'boolean'],
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
            'question_image' => 'Gambar Pertanyaan',
            'difficulty' => 'Tingkat Kesulitan',
            'weight' => 'Bobot Nilai',
            'status' => 'Status Soal',
            'options.A' => 'Pilihan Jawaban A',
            'options.B' => 'Pilihan Jawaban B',
            'options.C' => 'Pilihan Jawaban C',
            'options.D' => 'Pilihan Jawaban D',
            'options.E' => 'Pilihan Jawaban E',
            'option_images.A' => 'Gambar Pilihan A',
            'option_images.B' => 'Gambar Pilihan B',
            'option_images.C' => 'Gambar Pilihan C',
            'option_images.D' => 'Gambar Pilihan D',
            'option_images.E' => 'Gambar Pilihan E',
            'correct_option' => 'Kunci Jawaban Benar',
        ];
    }
}

