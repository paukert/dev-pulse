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

    public function getPullRequestQuery(): string
    {
        return <<<'GQL'
            query (
                $pullRequestID: MergeRequestID!
                $maxApproversCount: Int!
                $maxReviewersCount: Int!
                $maxActivitiesCount: Int!
            ) {
                mergeRequest(id: $pullRequestID) {
                    id
                    name
                    createdAt
                    author {
                        id
                        username
                    }
                    updatedAt
                    mergedAt
                    mergeUser {
                        id
                        username
                    }
                    closedAt
                    approvedBy(first: $maxApproversCount) {
                        pageInfo {
                            hasNextPage
                        }
                        nodes {
                            id
                            username
                        }
                    }
                    reviewers(first: $maxReviewersCount) {
                        pageInfo {
                            hasNextPage
                        }
                        nodes {
                            id
                            username
                        }
                    }
                    notes(first: $maxActivitiesCount) {
                        pageInfo {
                            endCursor
                            hasNextPage
                        }
                        nodes {
                            id
                            author {
                                id
                                username
                            }
                            body
                            createdAt
                            discussion {
                                id
                                resolvedAt
                                resolvable
                                resolvedBy {
                                    id
                                    username
                                }
                            }
                            system
                        }
                    }
                    diffStatsSummary {
                        additions
                        deletions
                        fileCount
                    }
                }
            }
        GQL;
    }

    public function getActivitiesQuery(): string
    {
        return <<<'GQL'
            query (
                $pullRequestID: MergeRequestID!
                $afterCursor: String!
                $maxActivitiesCount: Int!
            ) {
                mergeRequest(id: $pullRequestID) {
                    notes(first: $maxActivitiesCount, after: $afterCursor) {
                        pageInfo {
                            endCursor
                            hasNextPage
                        }
                        nodes {
                            id
                            author {
                                id
                                username
                            }
                            body
                            createdAt
                            discussion {
                                id
                                resolvedAt
                                resolvable
                                resolvedBy {
                                    id
                                    username
                                }
                            }
                            system
                        }
                    }
                }
            }
        GQL;
    }
}
