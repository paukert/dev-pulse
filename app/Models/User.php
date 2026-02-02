<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?Carbon $email_verified_at
 * @property string $password
 * @property ?string $remember_token
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property UserRole $role
 *
 * Relationships
 * @property Collection<int, VcsInstanceUser> $vcsInstanceUsers
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $attributes = [
        'role' => UserRole::USER->value,
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
            'role' => UserRole::class,
        ];
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * @return HasMany<VcsInstanceUser, $this>
     */
    public function vcsInstanceUsers(): HasMany
    {
        return $this->hasMany(VcsInstanceUser::class);
    }
}
