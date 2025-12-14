<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

use App\Services\GitProviders\Interfaces\QueryRepository;

final readonly class GitLabQueryRepository implements QueryRepository
{
    public function getRepositoriesQuery(): string
    {
        return <<<'GQL'
            query ($filterValue: String) {
                projects(search: $filterValue, sort: "name_asc") {
                    count
                    nodes {
                        id
                        name
                    }
                }
            }
        GQL;
    }
}
