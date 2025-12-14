<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

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
}
