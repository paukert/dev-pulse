<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PullRequestState;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $vcs_id
 * @property string $title
 * @property PullRequestState $state
 * @property CarbonImmutable $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $merged_at
 * @property ?Carbon $closed_at
 *
 * Foreign keys
 * @property int $repository_id
 * @property int $author_id
 * @property ?int $merged_by_user_id
 * @property ?int $closed_by_user_id
 *
 * Relationships
 * @property Repository $repository
 * @property VcsInstanceUser $author
 * @property ?VcsInstanceUser $mergedByUser
 * @property ?VcsInstanceUser $closedByUser
 * @property PullRequestMetrics $metrics
 * @property Collection<int, Reviewer> $reviewers
 */
class PullRequest extends Model
{
    /** @use HasFactory<\Database\Factories\PullRequestFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'state' => PullRequestState::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'datetime',
            'merged_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Repository, $this>
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * @return BelongsTo<VcsInstanceUser, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(VcsInstanceUser::class, 'author_id');
    }

    /**
     * @return BelongsTo<VcsInstanceUser, $this>
     */
    public function mergedByUser(): BelongsTo
    {
        return $this->belongsTo(VcsInstanceUser::class, 'merged_by_user_id');
    }

    /**
     * @return BelongsTo<VcsInstanceUser, $this>
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(VcsInstanceUser::class, 'closed_by_user_id');
    }

    /**
     * @return HasOne<PullRequestMetrics, $this>
     */
    public function metrics(): HasOne
    {
        return $this->hasOne(PullRequestMetrics::class);
    }

    /**
     * @return HasMany<Reviewer, $this>
     */
    public function reviewers(): HasMany
    {
        return $this->hasMany(Reviewer::class);
    }
}
