<?php

declare(strict_types=1);

namespace App\Enums;

enum VcsPlatform: string
{
    case GITHUB = 'github';
    case GITLAB = 'gitlab';

    public function getLabel(): string
    {
        return match ($this) {
            self::GITHUB => __('GitHub'),
            self::GITLAB => __('GitLab'),
        };
    }
}
