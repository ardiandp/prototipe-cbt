<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Current Active Academic Year
        $currentYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2027-06-25',
                'is_active' => true,
            ]
        );

        Semester::firstOrCreate(
            [
                'academic_year_id' => $currentYear->id,
                'name' => 'Ganjil',
            ],
            [
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
                'is_active' => true,
            ]
        );

        Semester::firstOrCreate(
            [
                'academic_year_id' => $currentYear->id,
                'name' => 'Genap',
            ],
            [
                'start_date' => '2027-01-05',
                'end_date' => '2027-06-25',
                'is_active' => false,
            ]
        );

        // 2. Previous Academic Year
        $pastYear = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-07-15',
                'end_date' => '2026-06-25',
                'is_active' => false,
            ]
        );

        Semester::firstOrCreate(
            [
                'academic_year_id' => $pastYear->id,
                'name' => 'Ganjil',
            ],
            [
                'start_date' => '2025-07-15',
                'end_date' => '2025-12-20',
                'is_active' => false,
            ]
        );

        Semester::firstOrCreate(
            [
                'academic_year_id' => $pastYear->id,
                'name' => 'Genap',
            ],
            [
                'start_date' => '2026-01-05',
                'end_date' => '2026-06-25',
                'is_active' => false,
            ]
        );
    }
}
