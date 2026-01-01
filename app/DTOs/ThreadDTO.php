<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Carbon;

final readonly class ThreadDTO
{
    public function __construct(
        public string $vcsId,
        public ?Carbon $resolvedAt,
        public ?UserDTO $resolvedBy,
    ) {
        //
    }
}
