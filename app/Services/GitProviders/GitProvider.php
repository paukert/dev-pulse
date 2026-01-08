<?php

declare(strict_types=1);

namespace App\Services\GitProviders;

use App\DTOs\PullRequest\PullRequestDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;
use App\Enums\VcsPlatform;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\VcsInstance;
use App\Services\GitProviders\Interfaces\Authenticator;
use App\Services\GitProviders\Interfaces\Mapper;
use App\Services\GitProviders\Interfaces\QueryRepository;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class GitProvider
{
    public function __construct(
        private Authenticator $authenticator,
        private Mapper $mapper,
        private QueryRepository $queryRepository,
        private VcsInstance $vcsInstance
    ) {
        //
    }

    private function getAuthenticatedRequest(): PendingRequest
    {
        $request = Http::createPendingRequest();
        $this->authenticator->authenticate($request, $this->vcsInstance);
        return $request;
    }

    public function getAvailableRepositories(?string $filterValue = null): RepositoriesListDTO
    {
        $filterValue = match ($this->vcsInstance->platform) {
            VcsPlatform::GITHUB => "$filterValue in:name sort:name-asc",
            default => $filterValue,
        };

        $request = $this->getAuthenticatedRequest();
        $response = $request->post(
            $this->vcsInstance->getGraphQLApiUrl(),
            [
                'query' => $this->queryRepository->getRepositoriesQuery(),
                'variables' => ['filterValue' => $filterValue]
            ]
        );

        return $this->mapper->mapRepositoryCollection($response->json());
    }

    public function syncRepository(Repository $repository): void
    {
        $repository->syncStartedAt = now();

        $updatedPullRequests = $this->getUpdatedPullRequests($repository);
        $this->syncPullRequests($repository, $updatedPullRequests);

        $repository->last_synced_at = $repository->syncStartedAt;
        $repository->save();
    }

    private function getUpdatedPullRequests(Repository $repository): PullRequestsListDTO
    {
        $pullRequestsList = [];
        $afterCursor = null;
        $syncFrom = $repository->last_synced_at ?? $repository->statistics_from;

        while (true) {
            $response = $this->getAuthenticatedRequest()->post(
                $this->vcsInstance->getGraphQLApiUrl(),
                [
                    'query' => $this->queryRepository->getUpdatedPullRequestsQuery(),
                    'variables' => [
                        'repositoryID' => $repository->vcs_id,
                        'updatedAfter' => $syncFrom->format(DateTimeInterface::ATOM),
                        'updatedBefore' => $repository->syncStartedAt->format(DateTimeInterface::ATOM),
                        'afterCursor' => $afterCursor,
                    ]
                ]
            );

            $pullRequestsPage = $this->mapper->mapPullRequestsPage($response->json(), $syncFrom, $repository->syncStartedAt);
            $pullRequestsList = array_merge($pullRequestsList, $pullRequestsPage->items);

            $afterCursor = $pullRequestsPage->pageInfo->endCursor;
            if ($pullRequestsPage->pageInfo->hasNextPage === false) {
                break;
            }
        }

        return new PullRequestsListDTO(items: $pullRequestsList);
    }

    private function syncPullRequests(Repository $repository, PullRequestsListDTO $pullRequests): void
    {
        DB::beginTransaction();

        try {
            foreach ($pullRequests->items as $pullRequest) {
                $response = $this->getAuthenticatedRequest()->post(
                    $this->vcsInstance->getGraphQLApiUrl(),
                    [
                        'query' => $this->queryRepository->getPullRequestQuery(),
                        'variables' => [
                            'pullRequestID' => $pullRequest['vcsId'],
                            'maxApproversCount' => PullRequest::MAX_SUPPORTED_APPROVERS_COUNT,
                            'maxReviewersCount' => PullRequest::MAX_SUPPORTED_REVIEWERS_COUNT,
                            'maxActivitiesCount' => PullRequest::MAX_ACTIVITIES_COUNT_PER_BATCH,
                        ],
                    ]
                );

                $pullRequestDTO = $this->mapper->mapPullRequest($response->json());
                $pullRequestDTO = $this->loadMissingActivities($pullRequestDTO);

                PullRequest::upsertFromDTO($pullRequestDTO, $repository);
                sleep(1);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function loadMissingActivities(PullRequestDTO $pullRequest): PullRequestDTO
    {
        while ($pullRequest->activities->pageInfo?->hasNextPage === true) {
            $response = $this->getAuthenticatedRequest()->post(
                $this->vcsInstance->getGraphQLApiUrl(),
                [
                    'query' => $this->queryRepository->getActivitiesQuery(),
                    'variables' => [
                        'pullRequestID' => $pullRequest->vcsId,
                        'afterCursor' => $pullRequest->activities->pageInfo->endCursor,
                        'maxActivitiesCount' => PullRequest::MAX_ACTIVITIES_COUNT_PER_BATCH,
                    ],
                ]
            );

            $pullRequest = $this->mapper->mapAndMergeAdditionalPullRequestActivities($response->json(), $pullRequest);

            sleep(1);
        }

        return $pullRequest;
    }
}
