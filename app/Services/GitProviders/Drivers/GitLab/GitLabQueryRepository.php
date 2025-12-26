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

    public function getUpdatedPullRequestsQuery(): string
    {
        return <<<'GQL'
            query ($repositoryID: ID!, $updatedAfter: Time, $updatedBefore: Time, $afterCursor: String) {
                projects(ids: [$repositoryID]) {
                    nodes {
                        mergeRequests(
                            updatedAfter: $updatedAfter
                            updatedBefore: $updatedBefore
                            first: 100
                            after: $afterCursor
                        ) {
                            pageInfo {
                                endCursor
                                hasNextPage
                            }
                            nodes {
                                id
                            }
                        }
                    }
                }
            }
        GQL;
    }
}
