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

    /**
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        $platforms = [];
        foreach (self::cases() as $platform) {
            $platforms[$platform->value] = $platform->getLabel();
        }
        return $platforms;
    }
}
