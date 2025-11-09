<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VcsPlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attributes
 * @property int $id
 * @property string $name
 * @property string $api_url
 * @property ?string $token
 * @property VcsPlatform $platform
 *
 * Relationships
 * @property Repository[] $repositories
 */
class VcsInstance extends Model
{
    /** @use HasFactory<\Database\Factories\VcsInstanceFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'platform' => VcsPlatform::class,
            'token' => 'encrypted',
        ];
    }

    /**
     * @return HasMany<Repository, $this>
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }
}
