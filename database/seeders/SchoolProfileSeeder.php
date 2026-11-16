<?php

namespace Database\Seeders;

use App\Models\SchoolProfile;
use Illuminate\Database\Seeder;

class SchoolProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchoolProfile::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'SMA Negeri 1 CBT Indonesia',
                'npsn' => '20109999',
                'address' => 'Jl. Pendidikan Mandiri No. 10, Jakarta Pusat, DKI Jakarta',
                'email' => 'info@sman1cbt.sch.id',
                'phone' => '021-5551234',
                'principal_name' => 'Drs. H. Mulyono, M.Pd.',
                'logo' => null,
            ]
        );
    }
}

