<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Carbon;

final readonly class CommentDTO
{
    public function __construct(
        public string $vcsId,
        public string $text,
        public UserDTO $author,
        public Carbon $createdAt,
        public ?string $threadVcsId = null,
    ) {
        //
    }
}
