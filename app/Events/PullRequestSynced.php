<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class PullRequestSynced implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param string[] $userVcsIds
     */
    public function __construct(
        public array $userVcsIds,
        public int $vcsInstanceId,
    ) {
        //
    }
}
