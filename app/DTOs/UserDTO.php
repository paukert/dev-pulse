<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class UserDTO
{
    public function __construct(
        public string $username,
        public string $vcsId,
    ) {
        //
    }
}
