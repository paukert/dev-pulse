<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PullRequestSynced;
use App\Services\Gamification\UserActivityCollector;
use Illuminate\Support\Facades\DB;

final readonly class ProcessUserActivities
{
    public function __construct(
        private UserActivityCollector $collector,
    ) {
        //
    }

    public function handle(PullRequestSynced $event): void
    {
        $userIds = DB::table('vcs_instance_users')
            ->select(['user_id'])
            ->whereIn('vcs_id', $event->userVcsIds)
            ->where('vcs_instance_id', '=', $event->vcsInstanceId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        $this->collector->addUserIds($userIds);
    }
}
