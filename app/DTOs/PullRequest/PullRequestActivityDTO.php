<?php

declare(strict_types=1);

namespace App\DTOs\PullRequest;

use App\DTOs\UserDTO;
use Illuminate\Support\Carbon;

final readonly class PullRequestActivityDTO
{
    public function __construct(
        public Carbon $performedAt,
        public UserDTO $user
    ) {
        //
    }
}
