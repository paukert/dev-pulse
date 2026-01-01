<?php

declare(strict_types=1);

namespace App\DTOs\PullRequest;

use App\DTOs\UserDTO;
use App\Enums\PullRequestState;
use Illuminate\Support\Carbon;

final readonly class PullRequestDTO
{
    public function __construct(
        public string $vcsId,
        public string $title,
        public PullRequestState $state,
        public Carbon $createdAt,
        public Carbon $updatedAt,
        public ?Carbon $mergedAt,
        public ?Carbon $closedAt,
        public UserDTO $author,
        public ?UserDTO $mergedByUser,
        public ?UserDTO $closedByUser,
        public PullRequestMetricsDTO $metrics,
        public PullRequestActivitiesListDTO $activities,
    ) {
        //
    }

    public function with(PullRequestActivitiesListDTO $activities): self
    {
        return clone($this, ['activities' => $activities]);
    }
}
