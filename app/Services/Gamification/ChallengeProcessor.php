<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\ChallengeActivityType;
use App\Models\Badge;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

final readonly class ChallengeProcessor
{
    public function __construct(
        private UserActivityCollector $collector,
    ) {
        //
    }

    public function evaluateNewActivities(): void
    {
        foreach ($this->collector->getUserIds() as $userId) {
            $this->evaluateChallengesForUser($userId);
        }
    }

    private function grantBadge(int $userId, int $challengeId): void
    {
        $badge = new Badge();
        $badge->user_id = $userId;
        $badge->challenge_id = $challengeId;
        $badge->earned_at = now();
        $badge->save();
    }

    private function evaluateChallengesForUser(int $userId): void
    {
        $vcsInstanceUserIds = null;
        $activeChallenges = Challenge::query()
            ->with(['activities'])
            ->whereDate('active_from', '<=', now())
            ->whereDate('active_to', '>=', now()->subDay())
            ->whereDoesntHave('badges', function (Builder $query) use ($userId): void {
                $query->where('user_id', '=', $userId);
            })->get();

        foreach ($activeChallenges as $challenge) {
            $vcsInstanceUserIds ??= DB::table('vcs_instance_users')
                ->where('user_id', '=', $userId)
                ->pluck('id')
                ->toArray();

            if ($this->getChallengeStatus($vcsInstanceUserIds, $challenge)['completed']) {
                $this->grantBadge($userId, $challenge->id);
            }
        }
    }

    /**
     * @param int[] $vcsInstanceUserIds
     * @return array{
     *     completed: boolean,
     *     progress: ?float,
     * }
     */
    public function getChallengeStatus(array $vcsInstanceUserIds, Challenge $challenge, bool $withProgress = false): array
    {
        $neededActionsCount = 0;
        $performedActionsCount = 0;
        $isCompleted = true;

        foreach ($challenge->activities as $activity) {
            $actionsCount = match ($activity->activity_type) {
                ChallengeActivityType::CREATE_PULL_REQUEST => $this->getCreatedPullRequestsCount($vcsInstanceUserIds, $challenge),
                ChallengeActivityType::MERGE_PULL_REQUEST => $this->getMergedPullRequestsCount($vcsInstanceUserIds, $challenge),
                ChallengeActivityType::SUBMIT_REVIEW => $this->getSubmittedReviewsCount($vcsInstanceUserIds, $challenge),
            };

            $neededActionsCount += $activity->needed_actions_count;
            $performedActionsCount += min($actionsCount, $activity->needed_actions_count);

            if ($actionsCount < $activity->needed_actions_count) {
                $isCompleted = false;
                if (!$withProgress) {
                    break;
                }
            }
        }

        return [
            'completed' => $isCompleted,
            'progress' => $withProgress ? (float)($performedActionsCount / $neededActionsCount) : null,
        ];
    }

    /**
     * @param int[] $vcsInstanceUserIds
     */
    private function getCreatedPullRequestsCount(array $vcsInstanceUserIds, Challenge $challenge): int
    {
        return DB::table('pull_requests')
            ->whereIn('author_id', $vcsInstanceUserIds)
            ->whereDate('created_at', '>=', $challenge->active_from)
            ->whereDate('created_at', '<=', $challenge->active_to)
            ->count();
    }

    /**
     * @param int[] $vcsInstanceUserIds
     */
    private function getMergedPullRequestsCount(array $vcsInstanceUserIds, Challenge $challenge): int
    {
        return DB::table('pull_requests')
            ->whereIn('merged_by_user_id', $vcsInstanceUserIds)
            ->whereDate('merged_at', '>=', $challenge->active_from)
            ->whereDate('merged_at', '<=', $challenge->active_to)
            ->count();
    }

    /**
     * @param int[] $vcsInstanceUserIds
     */
    private function getSubmittedReviewsCount(array $vcsInstanceUserIds, Challenge $challenge): int
    {
        $approves = DB::table('approvers')
            ->select(['pull_request_id', 'vcs_instance_user_id', 'approved_at AS event_at']);

        $actions = DB::table('comments')
            ->select(['pull_request_id', 'vcs_instance_user_id', 'created_at AS event_at'])
            ->unionAll($approves);

        return DB::table('reviewers')
            ->selectRaw('COUNT(DISTINCT(reviewers.pull_request_id)) AS count')
            ->joinSub($actions, 'actions', static function (JoinClause $join): void {
                $join->on('reviewers.pull_request_id', '=', 'actions.pull_request_id');
                $join->on('reviewers.vcs_instance_user_id', '=', 'actions.vcs_instance_user_id');
                $join->on('reviewers.assigned_at', '<=', 'actions.event_at');
            })
            ->whereIn('reviewers.vcs_instance_user_id', $vcsInstanceUserIds)
            ->whereDate('event_at', '>=', $challenge->active_from)
            ->whereDate('event_at', '<=', $challenge->active_to)
            ->value('count');
    }
}
