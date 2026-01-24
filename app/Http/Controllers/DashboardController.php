<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use stdClass;

class DashboardController extends Controller
{
    private const string DEVELOPER_STATS_PAGE_PARAM_NAME = 'developer_page',
        DEVELOPER_STATS_PER_PAGE_PARAM_NAME = 'developer_per_page';

    private const string TABLE_DEVELOPER_STATS = 'developerStats';

    private const string REVIEWER_STATS_PAGE_PARAM_NAME = 'reviewer_page',
        REVIEWER_STATS_PER_PAGE_PARAM_NAME = 'reviewer_per_page';

    private const string TABLE_REVIEWER_STATS = 'reviewerStats';

    public function index(Request $request): Response
    {
        $to = Carbon::make($request->query->get('to')) ?? Carbon::now()->modify('midnight');
        $from = Carbon::make($request->query->get('from')) ?? (clone $to)->subDays(7);
        $vcsInstanceUserIds = $request->user()->vcsInstanceUsers()->pluck('id')->toArray();

        return Inertia::render('Dashboard', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            self::TABLE_DEVELOPER_STATS => [
                'data' => fn () => $this->getDeveloperTableStats($request, $vcsInstanceUserIds, $from, $to),
                'config' => [
                    'id' => self::TABLE_DEVELOPER_STATS,
                    'pageParamName' => self::DEVELOPER_STATS_PAGE_PARAM_NAME,
                    'perPageParamName' => self::DEVELOPER_STATS_PER_PAGE_PARAM_NAME,
                ],
            ],
            self::TABLE_REVIEWER_STATS => [
                'data' => fn () => $this->getReviewerTableStats($request, $vcsInstanceUserIds, $from, $to),
                'config' => [
                    'id' => self::TABLE_REVIEWER_STATS,
                    'pageParamName' => self::REVIEWER_STATS_PAGE_PARAM_NAME,
                    'perPageParamName' => self::REVIEWER_STATS_PER_PAGE_PARAM_NAME,
                ],
            ],
        ]);
    }

    /**
     * @param int[] $vcsInstanceUserIds
     * @return AbstractPaginator<array-key, mixed>
     */
    private function getDeveloperTableStats(Request $request, array $vcsInstanceUserIds, CarbonInterface $from, CarbonInterface $to): AbstractPaginator
    {
        $perPage = $request->query->getInt(self::DEVELOPER_STATS_PER_PAGE_PARAM_NAME, 5);

        $commentsFromReviewersQuery = DB::table('comments')
            ->select('pull_request_id', DB::raw('COUNT(comments.id) as count'))
            ->join('pull_requests', 'comments.pull_request_id', '=', 'pull_requests.id')
            ->whereColumn('vcs_instance_user_id', '!=', 'pull_requests.author_id')
            ->groupBy('pull_request_id');

        return $this->getBaseTableStatsQuery($from, $to)
            ->addSelect(['comments_from_reviewers.count AS comments_from_reviewers_count'])
            ->leftJoinSub($commentsFromReviewersQuery, 'comments_from_reviewers', 'pull_requests.id', '=', 'comments_from_reviewers.pull_request_id')
            ->whereIn('pull_requests.author_id', $vcsInstanceUserIds)
            ->paginate(perPage: $perPage, pageName: self::DEVELOPER_STATS_PAGE_PARAM_NAME)
            ->through($this->getTransformFnForTableStats())
            ->withQueryString();
    }

    /**
     * @param int[] $vcsInstanceUserIds
     * @return AbstractPaginator<array-key, mixed>
     */
    private function getReviewerTableStats(Request $request, array $vcsInstanceUserIds, CarbonInterface $from, CarbonInterface $to): AbstractPaginator
    {
        $perPage = $request->query->getInt(self::REVIEWER_STATS_PER_PAGE_PARAM_NAME, 5);

        $commentsAsReviewerQuery = DB::table('comments')
            ->select('pull_request_id', DB::raw('COUNT(comments.id) as count'))
            ->whereIn('vcs_instance_user_id', $vcsInstanceUserIds)
            ->groupBy('pull_request_id');

        return $this->getBaseTableStatsQuery($from, $to)
            ->addSelect(['comments_as_reviewer.count AS comments_as_reviewer_count'])
            ->leftJoinSub($commentsAsReviewerQuery, 'comments_as_reviewer', 'pull_requests.id', '=', 'comments_as_reviewer.pull_request_id')
            ->join('reviewers', 'pull_requests.id', '=', 'reviewers.pull_request_id')
            ->whereIn('reviewers.vcs_instance_user_id', $vcsInstanceUserIds)
            ->paginate(perPage: $perPage, pageName: self::REVIEWER_STATS_PAGE_PARAM_NAME)
            ->through($this->getTransformFnForTableStats())
            ->withQueryString();
    }

    private function getTransformFnForTableStats(): Closure
    {
        return static fn (stdClass $pullRequest): array => [
            'title' => (string)$pullRequest->title,
            'state' => (string)$pullRequest->state,
            'created_at' => Carbon::parse($pullRequest->created_at)->format(DateTimeInterface::ATOM),
            'repository' => [
                'name' => (string)$pullRequest->repository_name,
            ],
            'author' => [
                'username' => (string)$pullRequest->author_username,
            ],
            'metrics' => [
                'added_lines' => (int)$pullRequest->added_lines,
                'deleted_lines' => (int)$pullRequest->deleted_lines,
                'files_count' => (int)$pullRequest->files_count,
                'merge_time' => $pullRequest->merge_time !== null ? (int)$pullRequest->merge_time : null,
                'time_to_review' => $pullRequest->time_to_review !== null ? (int)$pullRequest->time_to_review : null,
                'comments_from_reviewers_count' => isset($pullRequest->comments_from_reviewers_count) ? (int)$pullRequest->comments_from_reviewers_count : null,
                'comments_as_reviewer_count' => isset($pullRequest->comments_as_reviewer_count) ? (int)$pullRequest->comments_as_reviewer_count : null,
            ],
        ];
    }

    private function getBaseTableStatsQuery(CarbonInterface $from, CarbonInterface $to): Builder
    {
        $approves = DB::table('approvers')
            ->select(['pull_request_id', 'vcs_instance_user_id', 'approved_at AS event_at']);

        $actions = DB::table('comments')
            ->select(['pull_request_id', 'vcs_instance_user_id', 'created_at AS event_at'])
            ->unionAll($approves);

        $timeToReviewQuery = DB::table('reviewers')
            ->select([
                'reviewers.pull_request_id',
                DB::raw('TIMESTAMPDIFF(SECOND, MIN(reviewers.assigned_at), MIN(actions.event_at)) AS time_to_review'),
            ])
            ->leftJoinSub($actions, 'actions', static function (JoinClause $join): void {
                $join->on('reviewers.pull_request_id', '=', 'actions.pull_request_id');
                $join->on('reviewers.vcs_instance_user_id', '=', 'actions.vcs_instance_user_id');
                $join->on('reviewers.assigned_at', '<=', 'actions.event_at');
            })
            ->groupBy('reviewers.pull_request_id');

        return DB::table('pull_requests')
            ->select([
                'pull_requests.title',
                'pull_requests.state',
                'pull_requests.created_at',
                'repositories.name AS repository_name',
                'vcs_instance_users.username AS author_username',
                'pull_request_metrics.added_lines AS added_lines',
                'pull_request_metrics.deleted_lines AS deleted_lines',
                'pull_request_metrics.files_count AS files_count',
                'time_to_review.time_to_review',
                DB::raw('TIMESTAMPDIFF(SECOND, pull_requests.created_at, pull_requests.merged_at) AS merge_time'),
            ])
            ->join('repositories', 'pull_requests.repository_id', '=', 'repositories.id')
            ->join('vcs_instance_users', 'pull_requests.author_id', '=', 'vcs_instance_users.id')
            ->join('pull_request_metrics', 'pull_requests.id', '=', 'pull_request_metrics.pull_request_id')
            ->leftJoinSub($timeToReviewQuery, 'time_to_review', 'pull_requests.id', '=', 'time_to_review.pull_request_id')
            ->whereDate('pull_requests.updated_at', '>=', $from)
            ->whereDate('pull_requests.updated_at', '<=', $to)
            ->orderBy('pull_requests.updated_at', 'DESC');
    }
}
