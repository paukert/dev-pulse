<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Drivers\GitLab;

use App\Models\VcsInstance;
use App\Services\GitProviders\GitProvider;

final readonly class GitLabProviderFactory
{
    public function __construct(
        private GitLabAuthenticator $authenticator,
        private GitLabMapper $mapper,
        private GitLabQueryRepository $queryRepository,
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
