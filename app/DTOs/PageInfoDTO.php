<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class PageInfoDTO
{
    public function __construct(
        public ?bool $hasPreviousPage = null,
        public ?bool $hasNextPage = null,
        public ?string $startCursor = null,
        public ?string $endCursor = null,
    ) {
        //
    }
}
