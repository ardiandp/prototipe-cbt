<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'order',
    ];

    /**
     * Category options definition.
     */
    public const CATEGORIES = [
        'general' => 'Umum & LAN',
        'admin' => 'Admin Sekolah',
        'teacher' => 'Guru Pengampu',
        'student' => 'Siswa Peserta',
        'proctor' => 'Pengawas / Proktor',
        'tech' => 'Troubleshooting LAN',
    ];

    /**
     * Get readable category label.
     */
    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}

