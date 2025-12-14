<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class RepositoriesListDTO
{
    /**
     * @param array<array-key, array{vcsId: string, name: string}> $items
     */
    public function __construct(
        public array $items,
        public int $totalCount,
    ) {
        //
    }
}
