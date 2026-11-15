<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case STUDENT = 'student';

    /**
     * Get human-readable Indonesian label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMIN => 'Admin Sekolah',
            self::TEACHER => 'Guru',
            self::STUDENT => 'Siswa',
        };
    }

    /**
     * Get AdminLTE badge color class for the role.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'text-bg-danger',
            self::ADMIN => 'text-bg-primary',
            self::TEACHER => 'text-bg-warning',
            self::STUDENT => 'text-bg-success',
        };
    }

    /**
     * Array of all raw role values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

