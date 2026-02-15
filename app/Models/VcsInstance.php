<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VcsPlatform;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

/**
 * Attributes
 * @property int $id
 * @property string $name
 * @property string $api_url
 * @property ?string $token
 * @property ?string $installation_id
 * @property VcsPlatform $platform
 *
 * Relationships
 * @property Collection<int, Repository> $repositories
 */
class VcsInstance extends Model
{
    /** @use HasFactory<\Database\Factories\VcsInstanceFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'api_url',
        'token',
        'installation_id',
        'platform',
    ];

    protected function casts(): array
    {
        return [
            'platform' => VcsPlatform::class,
            'token' => 'encrypted',
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'api_url' => ['required', 'url', 'max:255'],
            'token' => ['required_if:platform,gitlab', 'nullable', 'string'],
            'installation_id' => ['required_if:platform,github', 'nullable', 'string', 'max:255'],
            'platform' => ['required', Rule::enum(VcsPlatform::class)],
        ];
    }

    /**
     * @return HasMany<Repository, $this>
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    /**
     * @return Attribute<string, string>
     */
    protected function apiUrl(): Attribute
    {
        return Attribute::make(
            get: static fn (string $value): string => rtrim($value, '/'),
            set: static fn (string $value): string => rtrim($value, '/'),
        );
    }

    public function getGraphQLApiUrl(): string
    {
        return $this->api_url . '/graphql';
    }
}
