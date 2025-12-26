<?php

declare(strict_types=1);

namespace App\DTOs\PullRequest;

use App\DTOs\PageInfoDTO;

final readonly class PullRequestsListDTO
{
    /**
     * @param array<array-key, array{vcsId: string}> $items
     */
    public function __construct(
        public array $items,
        public ?PageInfoDTO $pageInfo = null,
    ) {
        //
    }
}
