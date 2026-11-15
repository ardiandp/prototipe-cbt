<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultUsers = [
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@cbtschool.local',
                'role' => UserRole::SUPERADMIN,
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'name' => 'Admin Sekolah',
                'username' => 'admin',
                'email' => 'admin@cbtschool.local',
                'role' => UserRole::ADMIN,
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'name' => 'Guru Pengampu (Budi Santoso, S.Pd.)',
                'username' => 'guru',
                'email' => 'guru@cbtschool.local',
                'role' => UserRole::TEACHER,
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'name' => 'Siswa Peserta (Ahmad Dani)',
                'username' => 'siswa',
                'email' => 'siswa@cbtschool.local',
                'role' => UserRole::STUDENT,
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        ];

        foreach ($defaultUsers as $userData) {
            User::firstOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}
