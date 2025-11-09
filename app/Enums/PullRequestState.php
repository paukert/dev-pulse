<?php

declare(strict_types=1);

namespace App\Enums;

enum PullRequestState: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case MERGED = 'merged';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => __('Open'),
            self::CLOSED => __('Closed'),
            self::MERGED => __('Merged'),
        };
    }
}
