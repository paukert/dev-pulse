<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

use App\DTOs\PullRequest\PullRequestActivitiesListDTO;
use App\DTOs\PullRequest\PullRequestDTO;
use App\DTOs\PullRequest\PullRequestsListDTO;
use App\DTOs\RepositoriesListDTO;
use Carbon\CarbonInterface;

interface Mapper
{
    public function mapRepositoryCollection(array $data): RepositoriesListDTO;

    public function mapPullRequestsPage(array $data, CarbonInterface $from, CarbonInterface $to): PullRequestsListDTO;

    public function mapPullRequest(array $data): PullRequestDTO;

    public function mapAndMergeAdditionalPullRequestActivities(array $data, PullRequestActivitiesListDTO $activities): PullRequestActivitiesListDTO;
}
