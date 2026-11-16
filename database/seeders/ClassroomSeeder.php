<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::active() ?? AcademicYear::first();
        $rpl = Department::where('code', 'RPL')->first();
        $tkj = Department::where('code', 'TKJ')->first();
        $dkv = Department::where('code', 'DKV')->first();

        $classes = [
            // Kelas 10
            ['name' => 'X RPL 1', 'level' => '10', 'department_id' => $rpl?->id],
            ['name' => 'X RPL 2', 'level' => '10', 'department_id' => $rpl?->id],
            ['name' => 'X TKJ 1', 'level' => '10', 'department_id' => $tkj?->id],
            ['name' => 'X DKV 1', 'level' => '10', 'department_id' => $dkv?->id],

            // Kelas 11
            ['name' => 'XI RPL 1', 'level' => '11', 'department_id' => $rpl?->id],
            ['name' => 'XI RPL 2', 'level' => '11', 'department_id' => $rpl?->id],
            ['name' => 'XI TKJ 1', 'level' => '11', 'department_id' => $tkj?->id],
            ['name' => 'XI DKV 1', 'level' => '11', 'department_id' => $dkv?->id],

            // Kelas 12
            ['name' => 'XII RPL 1', 'level' => '12', 'department_id' => $rpl?->id],
            ['name' => 'XII TKJ 1', 'level' => '12', 'department_id' => $tkj?->id],
        ];

        foreach ($classes as $classData) {
            Classroom::firstOrCreate(
                [
                    'name' => $classData['name'],
                    'academic_year_id' => $activeYear?->id,
                ],
                array_merge($classData, [
                    'academic_year_id' => $activeYear?->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
