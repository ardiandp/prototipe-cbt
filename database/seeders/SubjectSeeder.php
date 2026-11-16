<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Muatan Nasional & Umum
            ['code' => 'MTK', 'name' => 'Matematika', 'level' => 'Semua', 'group' => 'Muatan Nasional'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'level' => 'Semua', 'group' => 'Muatan Nasional'],
            ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'level' => 'Semua', 'group' => 'Muatan Nasional'],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'level' => 'Semua', 'group' => 'Muatan Nasional'],
            ['code' => 'PKN', 'name' => 'Pendidikan Pancasila dan Kewarganegaraan', 'level' => 'Semua', 'group' => 'Muatan Nasional'],
            ['code' => 'SEJ', 'name' => 'Sejarah Indonesia', 'level' => 'Semua', 'group' => 'Muatan Nasional'],

            // Muatan Kejuruan - RPL
            ['code' => 'DDPK', 'name' => 'Dasar-Dasar Pengembangan Perangkat Lunak', 'level' => '10', 'group' => 'Muatan Peminatan Kejuruan'],
            ['code' => 'PWPB', 'name' => 'Pemrograman Web dan Perangkat Bergerak', 'level' => '11', 'group' => 'Muatan Peminatan Kejuruan'],
            ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek', 'level' => '11', 'group' => 'Muatan Peminatan Kejuruan'],
            ['code' => 'BD', 'name' => 'Basis Data', 'level' => '11', 'group' => 'Muatan Peminatan Kejuruan'],
            ['code' => 'PKK', 'name' => 'Produk Kreatif dan Kewirausahaan', 'level' => '12', 'group' => 'Muatan Peminatan Kejuruan'],

            // Muatan Kejuruan - TKJ
            ['code' => 'ASJ', 'name' => 'Administrasi Sistem Jaringan', 'level' => '11', 'group' => 'Muatan Peminatan Kejuruan'],
            ['code' => 'AIJ', 'name' => 'Administrasi Infrastruktur Jaringan', 'level' => '11', 'group' => 'Muatan Peminatan Kejuruan'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                array_merge($subject, ['is_active' => true])
            );
        }
    }
}
