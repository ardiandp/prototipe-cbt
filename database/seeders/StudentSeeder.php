<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classXRpl1 = Classroom::where('name', 'X RPL 1')->first() ?? Classroom::first();
        $classXRpl2 = Classroom::where('name', 'X RPL 2')->first() ?? $classXRpl1;
        $classXTkj1 = Classroom::where('name', 'X TKJ 1')->first() ?? $classXRpl1;
        $classXiRpl1 = Classroom::where('name', 'XI RPL 1')->first() ?? $classXRpl1;
        $classXiiRpl1 = Classroom::where('name', 'XII RPL 1')->first() ?? $classXRpl1;

        // 1. Link default demo student 'siswa'
        $demoUser = User::where('username', 'siswa')->first();
        if ($demoUser) {
            Student::firstOrCreate(
                ['user_id' => $demoUser->id],
                [
                    'classroom_id' => $classXRpl1?->id,
                    'nis' => '20261001',
                    'nisn' => '0081234501',
                    'name' => 'Ahmad Dani',
                    'gender' => 'L',
                    'birth_date' => '2009-04-12',
                    'phone' => '081234567801',
                    'address' => 'Jl. Pelajar Pejuang No. 1, Bandung',
                    'is_active' => true,
                ]
            );
        }

        // 2. Seed additional realistic students
        $additionalStudents = [
            // Kelas X RPL 1
            ['nis' => '20261002', 'nisn' => '0081234502', 'name' => 'Annisa Permata', 'gender' => 'P', 'class_id' => $classXRpl1?->id],
            ['nis' => '20261003', 'nisn' => '0081234503', 'name' => 'Bagas Pratama', 'gender' => 'L', 'class_id' => $classXRpl1?->id],
            ['nis' => '20261004', 'nisn' => '0081234504', 'name' => 'Citra Lestari', 'gender' => 'P', 'class_id' => $classXRpl1?->id],

            // Kelas X RPL 2
            ['nis' => '20261005', 'nisn' => '0081234505', 'name' => 'Dimas Saputra', 'gender' => 'L', 'class_id' => $classXRpl2?->id],
            ['nis' => '20261006', 'nisn' => '0081234506', 'name' => 'Eka Yulianti', 'gender' => 'P', 'class_id' => $classXRpl2?->id],

            // Kelas X TKJ 1
            ['nis' => '20261007', 'nisn' => '0081234507', 'name' => 'Fajar Nugraha', 'gender' => 'L', 'class_id' => $classXTkj1?->id],
            ['nis' => '20261008', 'nisn' => '0081234508', 'name' => 'Gita Maharani', 'gender' => 'P', 'class_id' => $classXTkj1?->id],

            // Kelas XI RPL 1
            ['nis' => '20251001', 'nisn' => '0071234501', 'name' => 'Hadi Kusuma', 'gender' => 'L', 'class_id' => $classXiRpl1?->id],
            ['nis' => '20251002', 'nisn' => '0071234502', 'name' => 'Indah Cahyani', 'gender' => 'P', 'class_id' => $classXiRpl1?->id],

            // Kelas XII RPL 1
            ['nis' => '20241001', 'nisn' => '0061234501', 'name' => 'Joko Widodo Putra', 'gender' => 'L', 'class_id' => $classXiiRpl1?->id],
            ['nis' => '20241002', 'nisn' => '0061234502', 'name' => 'Kurnia Ramadhani', 'gender' => 'P', 'class_id' => $classXiiRpl1?->id],
        ];

        foreach ($additionalStudents as $item) {
            $username = 'siswa.' . strtolower($item['nis']);
            $email = $username . '@cbtschool.local';

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => $item['name'],
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => UserRole::STUDENT,
                    'is_active' => true,
                ]
            );

            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'classroom_id' => $item['class_id'],
                    'nis' => $item['nis'],
                    'nisn' => $item['nisn'],
                    'name' => $item['name'],
                    'gender' => $item['gender'],
                    'birth_date' => '2008-08-17',
                    'phone' => '081234' . substr($item['nis'], -6),
                    'address' => 'Bandung, Jawa Barat',
                    'is_active' => true,
                ]
            );
        }
    }
}

