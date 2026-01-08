<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\PullRequest\PullRequestMetricsDTO;
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

    public static function upsertFromDTO(PullRequestMetricsDTO $metricsDTO, PullRequest $pullRequest): void
    {
        PullRequestMetrics::upsert(
            values: [
                'pull_request_id' => $pullRequest->id,
                'added_lines' => $metricsDTO->addedLines,
                'deleted_lines' => $metricsDTO->deletedLines,
                'files_count' => $metricsDTO->filesCount,
            ],
            uniqueBy: ['pull_request_id'],
            update: ['added_lines', 'deleted_lines', 'files_count']
        );
    }
}
