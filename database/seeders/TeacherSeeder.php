<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Link default demo teacher account 'guru'
        $demoUser = User::where('username', 'guru')->first();
        if ($demoUser) {
            $demoTeacher = Teacher::firstOrCreate(
                ['user_id' => $demoUser->id],
                [
                    'nip' => '198005122005011003',
                    'name' => 'Budi Santoso, S.Pd., M.Kom.',
                    'gender' => 'L',
                    'phone' => '081234567890',
                    'address' => 'Jl. Pendidikan No. 12, Bandung',
                    'is_active' => true,
                ]
            );

            // Assign subjects & classrooms
            $rplSubjects = Subject::whereIn('code', ['PWPB', 'PBO', 'BD', 'DDPK'])->pluck('id');
            $rplClasses = Classroom::where('name', 'like', '%RPL%')->pluck('id');

            $demoTeacher->subjects()->syncWithoutDetaching($rplSubjects);
            $demoTeacher->classrooms()->syncWithoutDetaching($rplClasses);
        }

        // 2. Seed additional realistic teachers
        $additionalTeachers = [
            [
                'username' => 'guru.matematika',
                'email' => 'siti.rahayu@cbtschool.local',
                'name' => 'Siti Rahayu, S.Pd.',
                'nip' => '198403152008012004',
                'gender' => 'P',
                'phone' => '081398765432',
                'address' => 'Jl. Merdeka No. 45, Bandung',
                'subject_codes' => ['MTK'],
                'class_names' => ['X RPL 1', 'X TKJ 1', 'XI RPL 1'],
            ],
            [
                'username' => 'guru.tkj',
                'email' => 'dedi.kurniawan@cbtschool.local',
                'name' => 'Dedi Kurniawan, S.T.',
                'nip' => '198708202010011005',
                'gender' => 'L',
                'phone' => '085712348899',
                'address' => 'Jl. Telekomunikasi No. 8, Bandung',
                'subject_codes' => ['ASJ', 'AIJ'],
                'class_names' => ['X TKJ 1', 'XI TKJ 1', 'XII TKJ 1'],
            ],
            [
                'username' => 'guru.bahasa',
                'email' => 'ratna.dewi@cbtschool.local',
                'name' => 'Ratna Dewi, M.Pd.',
                'nip' => '198211042006042002',
                'gender' => 'P',
                'phone' => '082155443322',
                'address' => 'Jl. Melati No. 17, Bandung',
                'subject_codes' => ['BIN'],
                'class_names' => ['X RPL 1', 'X DKV 1', 'XI DKV 1'],
            ],
            [
                'username' => 'guru.inggris',
                'email' => 'agus.prasetyo@cbtschool.local',
                'name' => 'Agus Prasetyo, S.Pd.',
                'nip' => '198906182014021001',
                'gender' => 'L',
                'phone' => '087811223344',
                'address' => 'Jl. Garuda No. 3, Bandung',
                'subject_codes' => ['BIG'],
                'class_names' => ['X TKJ 1', 'XI RPL 2', 'XII RPL 1'],
            ],
        ];

        foreach ($additionalTeachers as $item) {
            $user = User::firstOrCreate(
                ['username' => $item['username']],
                [
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::TEACHER,
                    'is_active' => true,
                ]
            );

            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $item['nip'],
                    'name' => $item['name'],
                    'gender' => $item['gender'],
                    'phone' => $item['phone'],
                    'address' => $item['address'],
                    'is_active' => true,
                ]
            );

            $subjects = Subject::whereIn('code', $item['subject_codes'])->pluck('id');
            $classrooms = Classroom::whereIn('name', $item['class_names'])->pluck('id');

            $teacher->subjects()->syncWithoutDetaching($subjects);
            $teacher->classrooms()->syncWithoutDetaching($classrooms);
        }
    }
}
