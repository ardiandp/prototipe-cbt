<?php

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
        /** @var Student $student */
        $student = $this->route('student');
        $userId = $student instanceof Student ? $student->user_id : null;
        $studentId = $student instanceof Student ? $student->id : $student;

        return [
            // Student Profile
            'name' => ['required', 'string', 'max:150'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($studentId)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($studentId)],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'gender' => ['nullable', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],

            // User Account Credentials
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:6'],
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
            'name' => 'Nama Lengkap Siswa',
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'classroom_id' => 'Kelas',
            'gender' => 'Jenis Kelamin',
            'birth_date' => 'Tanggal Lahir',
            'phone' => 'Nomor Telepon',
            'address' => 'Alamat',
            'username' => 'Username Akun',
            'email' => 'Email Akun',
            'password' => 'Password Akun',
            'is_active' => 'Status Aktif',
        ];
    }
}

