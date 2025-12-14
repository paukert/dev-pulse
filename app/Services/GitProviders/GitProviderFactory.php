<?php

declare(strict_types=1);

namespace App\Services\GitProviders;

use App\Enums\VcsPlatform;
use App\Models\VcsInstance;
use App\Services\GitProviders\Drivers\GitLab\GitLabProviderFactory;

final readonly class GitProviderFactory
{
    public function __construct(
        private GitLabProviderFactory $gitLabProviderFactory,
    ) {
        //
    }

    public function create(VcsInstance $vcsInstance): GitProvider
    {
        return match ($vcsInstance->platform) {
            VcsPlatform::GITLAB => $this->gitLabProviderFactory->create($vcsInstance),
            default => throw new \InvalidArgumentException('Unsupported platform'),
        };
    }
}
