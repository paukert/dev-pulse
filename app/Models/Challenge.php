<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $name
 * @property ?string $description
 * @property Carbon $active_from
 * @property Carbon $active_to
 *
 * Relationships
 * @property Collection<int, ChallengeActivity> $activities
 */
class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ChallengeActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ChallengeActivity::class);
    }
}
