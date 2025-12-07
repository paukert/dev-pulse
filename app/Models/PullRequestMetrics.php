<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attributes
 * @property int $added_lines
 * @property int $deleted_lines
 * @property int $files_count
 *
 * Foreign keys
 * @property int $pull_request_id
 *
 * Relationships
 * @property PullRequest $pullRequest
 */
class PullRequestMetrics extends Model
{
    /** @use HasFactory<\Database\Factories\PullRequestMetricsFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<PullRequest, $this>
     */
    public function pullRequest(): BelongsTo
    {
        return $this->belongsTo(PullRequest::class);
    }
}
