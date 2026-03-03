<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public const ROLE_BIBLIOTECARIO = 'bibliotecario';
    public const ROLE_ESTUDIANTE = 'estudiante';
    public const ROLE_DOCENTE = 'docente';

    public const LIBRARY_ROLES = [
        self::ROLE_BIBLIOTECARIO,
        self::ROLE_ESTUDIANTE,
        self::ROLE_DOCENTE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    public function isBibliotecario(): bool
    {
        return $this->hasRole(self::ROLE_BIBLIOTECARIO);
    }

    public function isDocente(): bool
    {
        return $this->hasRole(self::ROLE_DOCENTE);
    }

    public function isEstudiante(): bool
    {
        return $this->hasRole(self::ROLE_ESTUDIANTE);
    }

    public function hasLibraryRole(): bool
    {
        return $this->hasAnyRole(self::LIBRARY_ROLES);
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $defaultRoleExists = Role::query()
                ->where('name', self::ROLE_ESTUDIANTE)
                ->where('guard_name', 'web')
                ->exists();

            if ($defaultRoleExists && ! $user->hasLibraryRole()) {
                $user->assignRole(self::ROLE_ESTUDIANTE);
            }
        });
    }
}
