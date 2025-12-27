<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

use App\DTOs\PageInfoDTO;
use App\DTOs\PullRequest\PullRequestActivityDTO;
use App\DTOs\PullRequest\PullRequestDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\PullRequest\PullRequestMetricsDTO;
use App\DTOs\RepositoriesListDTO;
use App\DTOs\UserDTO;
use App\Enums\PullRequestState;
use App\Services\GitProviders\Interfaces\Mapper;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Log;
use LogicException;

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
    public function mapPullRequestsPage(array $data): PullRequestsListDTO
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
     *             notes: array{
     *                 pageInfo: array{startCursor: string, hasPreviousPage: bool},
     *                 nodes: array<array{
     *                     author: array{id: string, username: string},
     *                     body: string,
     *                     createdAt: string,
     *                     discussion: array{
     *                         id: string,
     *                         resolvedAt: ?string,
     *                         resolvable: bool,
     *                         resolvedBy: ?array{id: string, username: string},
     *                     },
     *                     system: bool,
     *                 }>,
     *             },
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

        $systemNotes = array_filter($data['notes']['nodes'], static fn (array $note): bool => $note['system']);
        $comments = array_filter($data['notes']['nodes'], static fn (array $note): bool => !$note['system']);

        $approvals = [];
        foreach ($data['approvedBy']['nodes'] as $approver) {
            $userDTO = new UserDTO(username: $approver['username'], vcsId: $approver['id']);
            $note = array_find(
                $systemNotes,
                static fn (array $note): bool => $note['author']['id'] === $userDTO->vcsId && $note['body'] === 'approved this merge request'
            ) ?? throw new LogicException('Date of approval not found in system notes.');
            $approvedAt = Carbon::createFromFormat(DateTimeInterface::ATOM, $note['createdAt']);
            $approvals[] = new PullRequestActivityDTO(performedAt: $approvedAt, user: $userDTO);
        }

        if ($data['approvedBy']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported approvers count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

        $reviews = [];
        foreach ($data['reviewers']['nodes'] as $reviewer) {
            $userDTO = new UserDTO(username: $reviewer['username'], vcsId: $reviewer['id']);
            $note = array_find(
                $systemNotes,
                static fn (array $note): bool => str_contains($note['body'], "requested review from @$userDTO->username")
            ) ?? throw new LogicException('Date of review request not found in system notes.');
            $requestedAt = Carbon::createFromFormat(DateTimeInterface::ATOM, $note['createdAt']);
            $reviews[] = new PullRequestActivityDTO(performedAt: $requestedAt, user: $userDTO);
        }

        if ($data['reviewers']['pageInfo']['hasNextPage']) {
            Log::warning('Max supported reviewers count exceeded (vcsId: {id})', [
                'id' => $data['id'],
            ]);
        }

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
            approvals: $approvals,
            reviews: $reviews,
        );
    }
}
