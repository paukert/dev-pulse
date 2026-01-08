<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\CommentDTO;
use Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $vcs_id
 * @property string $text
 * @property Carbon $created_at
 *
 * Foreign keys
 * @property int $pull_request_id
 * @property int $vcs_instance_user_id
 * @property ?int $thread_id
 *
 * Relationships
 * @property PullRequest $pullRequest
 * @property VcsInstanceUser $vcsInstanceUser
 * @property ?Thread $thread
 */
class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
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
     * @return BelongsTo<Thread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * @param CommentDTO[] $comments
     */
    public static function upsertFromDTOs(array $comments, PullRequest $pullRequest): void
    {
        $values = array_map(static fn (CommentDTO $comment): array => [
            'vcs_id' => $comment->vcsId,
            'text' => $comment->text,
            'created_at' => $comment->createdAt,
            'pull_request_id' => $pullRequest->id,
            'vcs_instance_user_id' => VcsInstanceUser::upsertFromDTO(userDTO: $comment->author, vcsInstance: $pullRequest->repository->vcsInstance),
            'thread_id' => $comment->threadVcsId !== null
                ? Cache::remember(
                    key: "thread-id-vcs-id-$comment->threadVcsId-pull-request-id-$pullRequest->id",
                    ttl: 24 * 60 * 60,
                    callback: static fn (): int => Thread::where(['vcs_id' => $comment->threadVcsId, 'pull_request_id' => $pullRequest->id])->first()->id
                )
                : null,
        ], $comments);

        Comment::upsert($values, ['vcs_id', 'pull_request_id'], ['text', 'thread_id']);
    }
}
