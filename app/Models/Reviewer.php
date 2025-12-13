<?php

declare(strict_types=1);

namespace App\Models;

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
}
