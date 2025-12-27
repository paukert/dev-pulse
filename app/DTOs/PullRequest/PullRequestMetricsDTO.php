<?php

declare(strict_types=1);

namespace App\DTOs\PullRequest;

final readonly class PullRequestMetricsDTO
{
    public function __construct(
        public int $addedLines,
        public int $deletedLines,
        public int $filesCount,
    ) {
        //
    }
}
