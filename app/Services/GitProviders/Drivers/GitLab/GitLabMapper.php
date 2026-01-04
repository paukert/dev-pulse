<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

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
use Log;

/**
 * @phpstan-type Notes array{
 *     pageInfo: array{endCursor: string, hasNextPage: bool},
 *     nodes: array<array{
 *         id: string,
 *         author: array{id: string, username: string},
 *         body: string,
 *         createdAt: string,
 *         discussion: array{
 *             id: string,
 *             resolvedAt: ?string,
 *             resolvable: bool,
 *             resolvedBy: ?array{id: string, username: string},
 *         },
 *         system: bool,
 *     }>,
 * }
 */
final readonly class GitLabMapper implements Mapper
{
    /**
     * @param array{
     *     data: array{
     *         projects: array{
     *             count: int,
     *             nodes: array<array{id: string, name: string}>,
     *         },
     *     },
     * } $data
     */
    public function mapRepositoryCollection(array $data): RepositoriesListDTO
    {
        $repositories = array_map(
            static fn (array $repository): array => [
                'vcsId' => $repository['id'],
                'name' => $repository['name'],
            ],
            $data['data']['projects']['nodes']
        );

        return new RepositoriesListDTO(
            items: $repositories,
            totalCount: $data['data']['projects']['count']
        );
    }

    /**
     * @param array{
     *     data: array{
     *         projects: array{
     *             nodes: array<array{
     *                 mergeRequests: array{
     *                     nodes: array<array{id: string}>,
     *                     pageInfo: array{endCursor: string, hasNextPage: bool},
     *                 },
     *             }>,
     *         },
     *     },
     * } $data
     */
    public function mapPullRequestsPage(array $data, CarbonInterface $from, CarbonInterface $to): PullRequestsListDTO
    {
        $pullRequestsData = $data['data']['projects']['nodes'][0]['mergeRequests'];

        $items = array_map(
            static fn (array $pullRequest): array => ['vcsId' => $pullRequest['id']],
            $pullRequestsData['nodes']
        );

        $pageInfo = new PageInfoDTO(
            hasNextPage: $pullRequestsData['pageInfo']['hasNextPage'],
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
     *         mergeRequest: array{
     *             id: string,
     *             name: string,
     *             createdAt: string,
     *             author: array{id: string, username: string},
     *             updatedAt: string,
     *             mergedAt: ?string,
     *             mergeUser: ?array{id: string, username: string},
     *             closedAt: ?string,
     *             approvedBy: array{
     *                 pageInfo: array{hasNextPage: bool},
     *                 nodes: array<array{id: string, username: string}>
     *             },
     *             reviewers: array{
     *                 pageInfo: array{hasNextPage: bool},
     *                 nodes: array<array{id: string, username: string}>
     *             },
     *             notes: Notes,
     *             diffStatsSummary: array{additions: int, deletions: int, fileCount: int},
     *         },
     *     },
     * } $data
     */
    public function mapPullRequest(array $data): PullRequestDTO
    {
        $data = $data['data']['mergeRequest'];

        $state = match (true) {
            $data['mergedAt'] !== null => PullRequestState::MERGED,
            $data['closedAt'] !== null => PullRequestState::CLOSED,
            default => PullRequestState::OPEN,
        };

        $approvals = [];
        foreach ($data['approvedBy']['nodes'] as $approver) {
            $userDTO = new UserDTO(username: $approver['username'], vcsId: $approver['id']);
            $approvals[] = new PullRequestActivityDTO(performedAt: null, user: $userDTO);
        }

        if ($data['approvedBy']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported approvers count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

        $reviews = [];
        foreach ($data['reviewers']['nodes'] as $reviewer) {
            $userDTO = new UserDTO(username: $reviewer['username'], vcsId: $reviewer['id']);
            $reviews[] = new PullRequestActivityDTO(performedAt: null, user: $userDTO);
        }

        if ($data['reviewers']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported reviewers count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

        $activities = $this->mapAndMergePullRequestActivities(
            $data['notes'],
            new PullRequestActivitiesListDTO(approvals: $approvals, reviews: $reviews, comments: [], threads: [])
        );

        return new PullRequestDTO(
            vcsId: $data['id'],
            title: $data['name'],
            state: $state,
            createdAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $data['createdAt']),
            updatedAt: Carbon::createFromFormat(DateTimeInterface::ATOM, $data['updatedAt']),
            mergedAt: $data['mergedAt'] !== null ? Carbon::createFromFormat(DateTimeInterface::ATOM, $data['mergedAt']) : null,
            closedAt: $data['closedAt'] !== null ? Carbon::createFromFormat(DateTimeInterface::ATOM, $data['closedAt']) : null,
            author: new UserDTO(username: $data['author']['username'], vcsId: $data['author']['id']),
            mergedByUser: $data['mergedAt'] !== null
                ? new UserDTO(username: $data['mergeUser']['username'], vcsId: $data['mergeUser']['id'])
                : null,
            // TODO: GitLab GraphQL API does not provide information about user who closed the MR (GitLab CE v17.5.1)
            closedByUser: null,
            metrics: new PullRequestMetricsDTO(
                addedLines: $data['diffStatsSummary']['additions'],
                deletedLines: $data['diffStatsSummary']['deletions'],
                filesCount: $data['diffStatsSummary']['fileCount'],
            ),
            activities: $activities,
        );
    }

    /**
     * @param Notes $notes
     */
    private function mapAndMergePullRequestActivities(
        array $notes,
        PullRequestActivitiesListDTO $activities
    ): PullRequestActivitiesListDTO {
        $systemNotes = array_filter($notes['nodes'], static fn (array $note): bool => $note['system']);
        $comments = array_filter($notes['nodes'], static fn (array $note): bool => !$note['system']);

        $approvals = [];
        foreach ($activities->approvals as $approval) {
            if ($approval->performedAt !== null) {
                $approvals[] = $approval;
                continue;
            }

            $note = array_find(
                $systemNotes,
                static fn (array $note): bool => $note['author']['id'] === $approval->user->vcsId && $note['body'] === 'approved this merge request'
            );
            $approvedAt = $note === null ? null : Carbon::createFromFormat(DateTimeInterface::ATOM, $note['createdAt']);
            $approvals[] = new PullRequestActivityDTO(performedAt: $approvedAt, user: $approval->user);
        }

        $reviews = [];
        foreach ($activities->reviews as $review) {
            if ($review->performedAt !== null) {
                $reviews[] = $review;
                continue;
            }

            $note = array_find(
                $systemNotes,
                static fn (array $note): bool => str_contains($note['body'], "requested review from @{$review->user->username}")
            );
            $requestedAt = $note === null ? null : Carbon::createFromFormat(DateTimeInterface::ATOM, $note['createdAt']);
            $reviews[] = new PullRequestActivityDTO(performedAt: $requestedAt, user: $review->user);
        }

        $threads = $activities->threads;
        $mappedComments = [];
        foreach ($comments as $comment) {
            $author = new UserDTO(username: $comment['author']['username'], vcsId: $comment['author']['id']);
            $createdAt = Carbon::createFromFormat(DateTimeInterface::ATOM, $comment['createdAt']);
            $discussion = $comment['discussion'];

            if ($discussion['resolvable'] && !isset($threads[$discussion['id']])) {
                $threads[$discussion['id']] = new ThreadDTO(
                    vcsId: $discussion['id'],
                    resolvedAt: $discussion['resolvedAt'] !== null
                        ? Carbon::createFromFormat(DateTimeInterface::ATOM, $discussion['resolvedAt'])
                        : null,
                    resolvedBy: $discussion['resolvedAt'] !== null
                        ? new UserDTO(username: $discussion['resolvedBy']['username'], vcsId: $discussion['resolvedBy']['id'])
                        : null,
                );
            }

            $mappedComments[] = new CommentDTO(
                vcsId: $comment['id'],
                text: $comment['body'],
                author: $author,
                createdAt: $createdAt,
                threadVcsId: isset($threads[$discussion['id']]) ? $discussion['id'] : null,
            );
        }

        return new PullRequestActivitiesListDTO(
            approvals: $approvals,
            reviews: $reviews,
            comments: array_merge($activities->comments, $mappedComments),
            threads: $threads,
            pageInfo: new PageInfoDTO(
                hasNextPage: $notes['pageInfo']['hasNextPage'],
                endCursor: $notes['pageInfo']['endCursor'],
            ),
        );
    }

    /**
     * @param array{
     *     data: array{
     *         mergeRequest: array{
     *             notes: Notes,
     *         },
     *     },
     * } $data
     */
    public function mapAndMergeAdditionalPullRequestActivities(
        array $data,
        PullRequestActivitiesListDTO $activities
    ): PullRequestActivitiesListDTO {
        return $this->mapAndMergePullRequestActivities($data['data']['mergeRequest']['notes'], $activities);
    }
}
