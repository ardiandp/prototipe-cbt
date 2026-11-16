<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'npsn',
        'address',
        'email',
        'phone',
        'principal_name',
        'logo',
    ];

    /**
     * Get or create the singleton school profile instance.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'name' => 'SMA Negeri 1 CBT Indonesia',
            'npsn' => '20109999',
            'address' => 'Jl. Pendidikan Mandiri No. 10, Jakarta Pusat',
            'email' => 'info@sman1cbt.sch.id',
            'phone' => '021-5551234',
            'principal_name' => 'Drs. H. Mulyono, M.Pd.',
        ]);
    }
}
