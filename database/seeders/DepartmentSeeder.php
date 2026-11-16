<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'RPL',
                'name' => 'Rekayasa Perangkat Lunak',
                'description' => 'Kompetensi keahlian yang mempelajari pengembangan perangkat lunak, pemrograman web, mobile, dan database.',
                'is_active' => true,
            ],
            [
                'code' => 'TKJ',
                'name' => 'Teknik Komputer dan Jaringan',
                'description' => 'Kompetensi keahlian yang mempelajari instalasi komputer, perakitan perangkat keras, administrasi server, dan konfigurasi jaringan.',
                'is_active' => true,
            ],
            [
                'code' => 'DKV',
                'name' => 'Desain Komunikasi Visual',
                'description' => 'Kompetensi keahlian yang fokus pada desain grafis, multimedia visual, animasi, dan ilustrasi digital.',
                'is_active' => true,
            ],
            [
                'code' => 'MPLB',
                'name' => 'Manajemen Perkantoran dan Layanan Bisnis',
                'description' => 'Kompetensi keahlian tata kelola administrasi perkantoran, komunikasi bisnis, kearsipan modern, dan otomasi kantor.',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}

