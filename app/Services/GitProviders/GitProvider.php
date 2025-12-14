<?php

declare(strict_types=1);

namespace App\Services\GitProviders;

use App\DTOs\RepositoriesListDTO;
use App\Models\VcsInstance;
use App\Services\GitProviders\Interfaces\Authenticator;
use App\Services\GitProviders\Interfaces\Mapper;
use App\Services\GitProviders\Interfaces\QueryRepository;
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
}
