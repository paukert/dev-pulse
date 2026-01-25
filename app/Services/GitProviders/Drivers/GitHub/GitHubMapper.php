<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitHub;

use App\DTOs\CommentDTO;
use App\DTOs\PageInfoDTO;
use App\DTOs\PullRequest\PullRequestActivitiesListDTO;
use App\DTOs\PullRequest\PullRequestActivityDTO;
use App\DTOs\PullRequest\PullRequestDTO;
use App\DTOs\PullRequest\PullRequestMetricsDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;
use App\DTOs\ThreadDTO;
use App\DTOs\UserDTO;
use App\Enums\PullRequestState;
use App\Services\GitProviders\Interfaces\Mapper;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @phpstan-type Actor array{
 *     id: string,
 *     login: string,
 * }
 *
 * @phpstan-type ReviewThreads array{
 *     pageInfo: array{hasNextPage: bool},
 *     nodes: list<array{
 *         id: string,
 *         isResolved: bool,
 *         resolvedBy: ?Actor,
 *         comments: array{
 *             pageInfo: array{hasNextPage: bool},
 *             nodes: list<array{
 *                 id: string,
 *                 body: string,
 *                 createdAt: string,
 *                 author: Actor,
 *             }>
 *         },
 *     }>
 * }
 *
 * @phpstan-type TimelineItems array{
 *     pageInfo: array{endCursor: ?string, hasNextPage: bool},
 *     nodes: list<
 *         array{
 *             __typename: 'ClosedEvent',
 *             actor: Actor,
 *         }|array{
 *             __typename: 'ReviewRequestedEvent',
 *             createdAt: string,
 *             requestedReviewer: Actor|array{id: string, name: string},
 *         }|array{
 *             __typename: 'PullRequestReview'|'IssueComment',
 *             id: string,
 *             body: string,
 *             createdAt: string,
 *             author: Actor,
 *         },
 *     >
 * }
 */
final readonly class GitHubMapper implements Mapper
{
    /**
     * @param array{
     *     data: array{
     *         search: array{
     *             repositoryCount: int,
     *             nodes: list<array{id: string, nameWithOwner: string}>,
     *         },
     *     },
     * } $data
     */
    public function mapRepositoryCollection(array $data): RepositoriesListDTO
    {
        $repositories = array_map(
            static fn (array $repository): array => [
                'vcsId' => $repository['id'],
                'name' => $repository['nameWithOwner'],
            ],
            $data['data']['search']['nodes']
        );

        return new RepositoriesListDTO(
            items: $repositories,
            totalCount: $data['data']['search']['repositoryCount']
        );
    }

    /**
     * @param array{
     *     data: array{
     *         node: array{
     *             pullRequests: array{
     *                 pageInfo: array{endCursor: string, hasNextPage: bool},
     *                 nodes: list<array{id: string, updatedAt: string}>,
     *             },
     *         },
     *     },
     * } $data
     */
    public function mapPullRequestsPage(array $data, CarbonInterface $from, CarbonInterface $to): PullRequestsListDTO
    {
        $pullRequestsData = $data['data']['node']['pullRequests'];

        $dataPriorFromFound = false;
        $items = [];

        foreach ($pullRequestsData['nodes'] as $pullRequest) {
            if (Carbon::parse($pullRequest['updatedAt']) < $from) {
                $dataPriorFromFound = true;
                break;
            }

            if (Carbon::parse($pullRequest['updatedAt']) > $to) {
                continue;
            }

            $items[] = ['vcsId' => $pullRequest['id']];
        }

        $pageInfo = new PageInfoDTO(
            hasNextPage: $dataPriorFromFound ? false : $pullRequestsData['pageInfo']['hasNextPage'],
            endCursor: $pullRequestsData['pageInfo']['endCursor'],
        );

        return new PullRequestsListDTO(
            items: $items,
            pageInfo: $pageInfo,
        );
    }

    /**
     * @param array{
     *     data: array{
     *         node: array{
     *             id: string,
     *             title: string,
     *             createdAt: string,
     *             author: Actor,
     *             updatedAt: string,
     *             mergedAt: ?string,
     *             mergedBy: ?Actor,
     *             closedAt: ?string,
     *             state: 'OPEN'|'CLOSED'|'MERGED',
     *             additions: int,
     *             deletions: int,
     *             changedFiles: int,
     *             reviews: array{
     *                 pageInfo: array{hasNextPage: bool},
     *                 nodes: list<array{
     *                     submittedAt: string,
     *                     author: Actor,
     *                 }>
     *             },
     *             reviewThreads: ReviewThreads,
     *             timelineItems: TimelineItems,
     *         },
     *     },
     * } $data
     */
    public function mapPullRequest(array $data): PullRequestDTO
    {
        $data = $data['data']['node'];

        $approvals = [];
        foreach ($data['reviews']['nodes'] as $approve) {
            $approvals[] = new PullRequestActivityDTO(
                performedAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $approve['submittedAt']),
                user: new UserDTO(username: $approve['author']['login'], vcsId: $approve['author']['id']),
            );
        }

        if ($data['reviews']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported approvers count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

        $comments = [];
        $threads = [];
        foreach ($data['reviewThreads']['nodes'] as $thread) {
            $threads[$thread['id']] = new ThreadDTO(
                vcsId: $thread['id'],
                // GitHub does not provide information about thread resolution date
                resolvedAt: $thread['isResolved'] ? now() : null,
                resolvedBy: $thread['isResolved']
                    ? new UserDTO(username: $thread['resolvedBy']['login'], vcsId: $thread['resolvedBy']['id'])
                    : null,
            );

            foreach ($thread['comments']['nodes'] as $comment) {
                $comments[] = new CommentDTO(
                    vcsId: $comment['id'],
                    text: $comment['body'],
                    author: new UserDTO(username: $comment['author']['login'], vcsId: $comment['author']['id']),
                    createdAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $comment['createdAt']),
                    threadVcsId: $thread['id'],
                );
            }

            if ($thread['comments']['pageInfo']['hasNextPage']) {
                Log::warning('Max supported comments count exceeded (threadId: {id})', [
                    'id' => $thread['id'],
                ]);
            }
        }

        if ($data['reviewThreads']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported reviewThreads count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

        $pullRequest = new PullRequestDTO(
            vcsId: $data['id'],
            title: $data['title'],
            state: PullRequestState::from(mb_strtolower($data['state'])),
            createdAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $data['createdAt']),
            updatedAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $data['updatedAt']),
            mergedAt: $data['mergedAt'] !== null ? Carbon::createFromFormat(DateTimeInterface::ATOM, $data['mergedAt']) : null,
            closedAt: $data['closedAt'] !== null ? Carbon::createFromFormat(DateTimeInterface::ATOM, $data['closedAt']) : null,
            author: new UserDTO(username: $data['author']['login'], vcsId: $data['author']['id']),
            mergedByUser: $data['mergedAt'] !== null
                ? new UserDTO(username: $data['mergedBy']['login'], vcsId: $data['mergedBy']['id'])
                : null,
            closedByUser: null,
            metrics: new PullRequestMetricsDTO(
                addedLines: $data['additions'],
                deletedLines: $data['deletions'],
                filesCount: $data['changedFiles'],
            ),
            activities: new PullRequestActivitiesListDTO($approvals, [], $comments, $threads),
        );

        return $this->mapAndMergePullRequestActivities($data['timelineItems'], $pullRequest);
    }

    /**
     * @param TimelineItems $timelineItems
     */
    private function mapAndMergePullRequestActivities(
        array $timelineItems,
        PullRequestDTO $pullRequest
    ): PullRequestDTO {
        $reviews = $pullRequest->activities->reviews;
        $comments = $pullRequest->activities->comments;

        foreach ($timelineItems['nodes'] as $timelineItem) {
            if ($timelineItem['__typename'] === 'ClosedEvent') {
                $closedBy = new UserDTO(username: $timelineItem['actor']['login'], vcsId: $timelineItem['actor']['id']);
                $pullRequest = $pullRequest->with(closedByUser: $closedBy);
                continue;
            }

            if ($timelineItem['__typename'] === 'ReviewRequestedEvent') {
                $reviews[] = new PullRequestActivityDTO(
                    performedAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $timelineItem['createdAt']),
                    user: new UserDTO(username: $timelineItem['requestedReviewer']['login'], vcsId: $timelineItem['requestedReviewer']['id']),
                );
                continue;
            }

            if (in_array($timelineItem['__typename'], ['PullRequestReview', 'IssueComment'])) {
                // when user just approves PR, there is no body in the PullRequestReview item
                if ($timelineItem['body'] === '') {
                    continue;
                }

                $comments[] = new CommentDTO(
                    vcsId: $timelineItem['id'],
                    text: $timelineItem['body'],
                    author: new UserDTO(username: $timelineItem['author']['login'], vcsId: $timelineItem['author']['id']),
                    createdAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $timelineItem['createdAt']),
                );
                continue;
            }

            throw new RuntimeException("Unknown timeline item type: {$timelineItem['__typename']}");
        }

        $activities = new PullRequestActivitiesListDTO(
            approvals: $pullRequest->activities->approvals,
            reviews: $reviews,
            comments: $comments,
            threads: $pullRequest->activities->threads,
            pageInfo: new PageInfoDTO(
                hasNextPage: $timelineItems['pageInfo']['hasNextPage'],
                endCursor: $timelineItems['pageInfo']['endCursor'],
            ),
        );

        return $pullRequest->with(activities: $activities);
    }

    /**
     * @param array{
     *     data: array{
     *         node: array{
     *             timelineItems: TimelineItems,
     *         },
     *     },
     * } $data
     */
    public function mapAndMergeAdditionalPullRequestActivities(array $data, PullRequestDTO $pullRequest): PullRequestDTO
    {
        $data = $data['data']['node'];

        return $this->mapAndMergePullRequestActivities($data['timelineItems'], $pullRequest);
    }
}
