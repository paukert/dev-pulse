<?php

declare(strict_types=1);

namespace App\Enums;

enum ChallengeActivityType: string
{
    case CREATE_PULL_REQUEST = 'create_pull_request';
    case MERGE_PULL_REQUEST = 'merge_pull_request';
    case SUBMIT_REVIEW = 'submit_review';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATE_PULL_REQUEST => __('Create a pull request'),
            self::MERGE_PULL_REQUEST => __('Merge a pull request'),
            self::SUBMIT_REVIEW => __('Submit a review'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        $types = [];
        foreach (self::cases() as $type) {
            $types[$type->value] = $type->getLabel();
        }
        return $types;
    }
}
