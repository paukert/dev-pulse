<?php

declare(strict_types=1);

namespace App\DTOs\PullRequest;

use App\DTOs\CommentDTO;
use App\DTOs\PageInfoDTO;
use App\DTOs\ThreadDTO;

final readonly class PullRequestActivitiesListDTO
{
    /**
     * @param array<PullRequestActivityDTO> $approvals
     * @param array<PullRequestActivityDTO> $reviews
     * @param array<CommentDTO> $comments
     * @param array<ThreadDTO> $threads
     */
    public function __construct(
        public array $approvals,
        public array $reviews,
        public array $comments,
        public array $threads,
        public ?PageInfoDTO $pageInfo = null,
    ) {
        //
    }
}
