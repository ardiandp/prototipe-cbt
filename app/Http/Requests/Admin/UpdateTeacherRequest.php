<?php

namespace App\Http\Requests\Admin;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
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
        /** @var Teacher $teacher */
        $teacher = $this->route('teacher');
        $userId = $teacher instanceof Teacher ? $teacher->user_id : null;
        $teacherId = $teacher instanceof Teacher ? $teacher->id : $teacher;

        return [
            // Teacher Profile
            'name' => ['required', 'string', 'max:150'],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'nip')->ignore($teacherId)],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],

            // User Account Credentials
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:6'],

            // Teaching Assignments
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => ['exists:classrooms,id'],
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
            'name' => 'Nama Lengkap Guru',
            'nip' => 'NIP',
            'gender' => 'Jenis Kelamin',
            'phone' => 'Nomor Telepon',
            'address' => 'Alamat',
            'username' => 'Username Akun',
            'email' => 'Email Akun',
            'password' => 'Password Akun',
            'subject_ids' => 'Mata Pelajaran',
            'classroom_ids' => 'Kelas Diampu',
        ];
    }
}
