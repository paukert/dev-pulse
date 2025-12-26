<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

use App\DTOs\PageInfoDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;
use App\Services\GitProviders\Interfaces\Mapper;

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
}
