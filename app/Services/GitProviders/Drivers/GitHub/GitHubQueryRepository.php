<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitHub;

use App\Services\GitProviders\Interfaces\QueryRepository;

final readonly class GitHubQueryRepository implements QueryRepository
{
    public function getRepositoriesQuery(): string
    {
        return <<<'GQL'
            query ($filterValue: String!) {
                search(query: $filterValue, type: REPOSITORY, first: 50) {
                    repositoryCount
                    nodes {
                        ... on Repository {
                            id
                            nameWithOwner
                        }
                    }
                }
            }
        GQL;
    }

    public function getUpdatedPullRequestsQuery(): string
    {
        return <<<'GQL'
            query ($repositoryID: ID!, $afterCursor: String) {
                node(id: $repositoryID) {
                    ... on Repository {
                        pullRequests(
                            first: 100
                            after: $afterCursor
                            orderBy: { field: UPDATED_AT, direction: DESC }
                        ) {
                            pageInfo {
                                endCursor
                                hasNextPage
                            }
                            nodes {
                                id
                                updatedAt
                            }
                        }
                    }
                }
            }
        GQL;
    }

    public function getPullRequestQuery(): string
    {
        $query = <<<'GQL'
            query (
                $pullRequestID: ID!
                $afterCursor: String
                $maxApproversCount: Int!
                $maxActivitiesCount: Int!
            ) {
                node(id: $pullRequestID) {
                    ... on PullRequest {
                        id
                        title
                        createdAt
                        author {
                            ...ActorFields
                        }
                        updatedAt
                        mergedAt
                        mergedBy {
                            ...ActorFields
                        }
                        closedAt
                        state
                        reviews(first: $maxApproversCount, states: [APPROVED]) {
                            pageInfo {
                                hasNextPage
                            }
                            nodes {
                                author {
                                    ...ActorFields
                                }
                                submittedAt
                            }
                        }
                        reviewThreads(first: 100) {
                            pageInfo {
                                hasNextPage
                            }
                            nodes {
                                id
                                # GitHub does not provide timestamp when was thread resolved
                                isResolved
                                resolvedBy {
                                    ...ActorFields
                                }
                                comments(first: 100) {
                                    pageInfo {
                                        hasNextPage
                                    }
                                    nodes {
                                        id
                                        author {
                                            ...ActorFields
                                        }
                                        body
                                        createdAt
                                    }
                                }
                            }
                        }
                        %activitiesQueryFragment%
                        additions
                        deletions
                        changedFiles
                    }
                }
            }

            %actorsFieldQueryFragment%
        GQL;

        return strtr($query, [
            '%activitiesQueryFragment%' => $this->getActivitiesQueryFragment(),
            '%actorsFieldQueryFragment%' => $this->getActorsFieldQueryFragment(),
        ]);
    }

    private function getActivitiesQueryFragment(): string
    {
        return <<<'GQL'
            timelineItems(
                after: $afterCursor
                first: $maxActivitiesCount
                itemTypes: [
                    CLOSED_EVENT
                    ISSUE_COMMENT
                    PULL_REQUEST_REVIEW
                    REVIEW_REQUESTED_EVENT
                ]
            ) {
                pageInfo {
                    endCursor
                    hasNextPage
                }
                nodes {
                    __typename
                    ... on ClosedEvent {
                        actor {
                            ...ActorFields
                        }
                    }
                    ... on ReviewRequestedEvent {
                        createdAt
                        requestedReviewer {
                            ...ActorFields
                            ... on Team {
                                id
                                name
                            }
                        }
                    }
                    ... on PullRequestReview {
                        id
                        author {
                            ...ActorFields
                        }
                        body
                        createdAt
                    }
                    ... on IssueComment {
                        id
                        author {
                            ...ActorFields
                        }
                        body
                        createdAt
                    }
                }
            }
        GQL;
    }

    private function getActorsFieldQueryFragment(): string
    {
        return <<<'GQL'
            fragment ActorFields on Actor {
                ... on User {
                    id
                    login
                }
                ... on Bot {
                    id
                    login
                }
            }
        GQL;
    }

    public function getActivitiesQuery(): string
    {
        $query = <<<'GQL'
            query (
                $pullRequestID: ID!
                $afterCursor: String
                $maxActivitiesCount: Int!
            ) {
                node(id: $pullRequestID) {
                    ... on PullRequest {
                        %activitiesQueryFragment%
                    }
                }
            }

            %actorsFieldQueryFragment%
        GQL;

        return strtr($query, [
            '%activitiesQueryFragment%' => $this->getActivitiesQueryFragment(),
            '%actorsFieldQueryFragment%' => $this->getActorsFieldQueryFragment(),
        ]);
    }
}
