<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\PullRequest\PullRequestActivityDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property Carbon $assigned_at
 *
 * Foreign keys
 * @property int $pull_request_id
 * @property int $vcs_instance_user_id
 *
 * Relationships
 * @property PullRequest $pullRequest
 * @property VcsInstanceUser $vcsInstanceUser
 */
class Reviewer extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewerFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
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
    public function vcsInstanceUser(): BelongsTo
    {
        return $this->belongsTo(VcsInstanceUser::class);
    }

    /**
     * @param PullRequestActivityDTO[] $reviewers
     */
    public static function upsertFromDTOs(array $reviewers, PullRequest $pullRequest): void
    {
        $values = array_map(static fn (PullRequestActivityDTO $reviewer): array => [
            'assigned_at' => $reviewer->performedAt,
            'pull_request_id' => $pullRequest->id,
            'vcs_instance_user_id' => VcsInstanceUser::upsertFromDTO(userDTO: $reviewer->user, vcsInstance: $pullRequest->repository->vcsInstance),
        ], $reviewers);

        Reviewer::upsert(values: $values, uniqueBy: ['pull_request_id', 'vcs_instance_user_id'], update: ['assigned_at']);
    }
}
