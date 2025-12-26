<?php

declare(strict_types=1);

namespace App\Services\GitProviders;

use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;
use App\Models\Repository;
use App\Models\VcsInstance;
use App\Services\GitProviders\Interfaces\Authenticator;
use App\Services\GitProviders\Interfaces\Mapper;
use App\Services\GitProviders\Interfaces\QueryRepository;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

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
        $request = $this->getAuthenticatedRequest();
        $response = $request->post(
            $this->vcsInstance->api_url,
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
                $this->vcsInstance->api_url,
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

            $pullRequestsPage = $this->mapper->mapPullRequestsPage($response->json());
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
        //
    }
}
