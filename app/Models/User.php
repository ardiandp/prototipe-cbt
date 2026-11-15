<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPERADMIN;
    }

    /**
     * Check if user is School Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user has administrative privileges (Super Admin or School Admin).
     */
    public function isAnyAdmin(): bool
    {
        return in_array($this->role, [UserRole::SUPERADMIN, UserRole::ADMIN], true);
    }

    /**
     * Check if user is Teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === UserRole::TEACHER;
    }

    /**
     * Check if user is Student.
     */
    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    /**
     * Check if user has one or more given roles.
     *
     * @param string|array<string|UserRole>|UserRole $roles
     */
    public function hasRole(string|array|UserRole $roles): bool
    {
        if ($roles instanceof UserRole) {
            return $this->role === $roles;
        }

        if (is_string($roles)) {
            return $this->role->value === $roles || $this->role->name === strtoupper($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get human-readable Indonesian label for user's role.
     */
    public function roleLabel(): string
    {
        return $this->role?->label() ?? 'Pengguna';
    }

    /**
     * Get AdminLTE badge color class for user's role.
     */
    public function roleBadgeClass(): string
    {
        return $this->role?->badgeClass() ?? 'text-bg-secondary';
    }
}
