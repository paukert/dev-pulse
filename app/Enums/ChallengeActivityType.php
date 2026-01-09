<?php

declare(strict_types=1);

namespace App\Enums;

enum ChallengeActivityType: string
{
    case CREATE_PULL_REQUEST = 'create_pull_request';
    case MERGE_PULL_REQUEST = 'merge_pull_request';
    case COMPLETE_REVIEW = 'complete_review';
}
