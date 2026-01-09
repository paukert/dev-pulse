<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChallengeActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 * @property int $id
 * @property int $needed_actions_count
 * @property ChallengeActivityType $activity_type
 *
 * Foreign keys
 * @property int $challenge_id
 *
 * Relationships
 * @property Challenge $challenge
 */
class ChallengeActivity extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeActivityFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'activity_type' => ChallengeActivityType::class,
        ];
    }

    /**
     * @return BelongsTo<Challenge, $this>
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
