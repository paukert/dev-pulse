<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\ThreadDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $vcs_id
 * @property ?Carbon $resolved_at
 *
 * Foreign keys
 * @property ?int $resolved_by_user_id
 * @property int $pull_request_id
 *
 * Relationships
 * @property ?VcsInstanceUser $resolvedByUser
 * @property PullRequest $pullRequest
 */
class Thread extends Model
{
    /** @use HasFactory<\Database\Factories\ThreadFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PullRequest, $this>
     */
    public function pullRequest(): BelongsTo
    {
        return $this->belongsTo(PullRequest::class);
    }

    /**
     * @return BelongsTo<VcsInstanceUser, $this>
     */
    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(VcsInstanceUser::class, 'resolved_by_user_id');
    }

    /**
     * @param ThreadDTO[] $threads
     */
    public static function upsertFromDTOs(array $threads, PullRequest $pullRequest): void
    {
        $values = array_map(static fn (ThreadDTO $thread): array => [
            'vcs_id' => $thread->vcsId,
            'resolved_at' => $thread->resolvedAt,
            'resolved_by_user_id' => $thread->resolvedBy !== null
                ? VcsInstanceUser::upsertFromDTO(userDTO: $thread->resolvedBy, vcsInstance: $pullRequest->repository->vcsInstance)
                : null,
            'pull_request_id' => $pullRequest->id,
        ], $threads);

        Thread::upsert(
            values: $values,
            uniqueBy: ['vcs_id', 'pull_request_id'],
            update: ['resolved_at', 'resolved_by_user_id']
        );
    }
}
