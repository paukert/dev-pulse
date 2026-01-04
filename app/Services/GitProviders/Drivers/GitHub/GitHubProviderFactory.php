<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitHub;

use App\Models\VcsInstance;
use App\Services\GitProviders\GitProvider;

final readonly class GitHubProviderFactory
{
    public function __construct(
        private GitHubAuthenticator $authenticator,
        private GitHubMapper $mapper,
        private GitHubQueryRepository $queryRepository,
    ) {
        //
    }

    public function create(VcsInstance $vcsInstance): GitProvider
    {
        return new GitProvider(
            authenticator: $this->authenticator,
            mapper: $this->mapper,
            queryRepository: $this->queryRepository,
            vcsInstance: $vcsInstance,
        );
    }
}
