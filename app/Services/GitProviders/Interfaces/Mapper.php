<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

use App\DTOs\PullRequest\PullRequestDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;

interface Mapper
{
    public function mapRepositoryCollection(array $data): RepositoriesListDTO;

    public function mapPullRequestsPage(array $data): PullRequestsListDTO;

    public function mapPullRequest(array $data): PullRequestDTO;
}
