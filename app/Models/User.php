<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'avatar_path',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->role_id === 1 && !$user->hasRole('admin')) {
                $user->syncRoles(['admin']);
            } elseif ($user->role_id === 2 && !$user->hasRole('faculty')) {
                $user->syncRoles(['faculty']);
            } elseif ($user->role_id === 3 && !$user->hasRole('accreditor')) {
                $user->syncRoles(['accreditor']);
            }
        });
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_user')
            ->withPivot('assignment_role', 'assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || (int)$this->role_id === 1;
    }

    public function isFaculty(): bool
    {
        return $this->hasRole('faculty') || (int)$this->role_id === 2;
    }

    public function isAccreditor(): bool
    {
        return $this->hasRole('accreditor') || (int)$this->role_id === 3;
    }

    public function complianceReportsCreated()
    {
        return $this->hasMany(ComplianceReport::class, 'created_by');
    }
}
