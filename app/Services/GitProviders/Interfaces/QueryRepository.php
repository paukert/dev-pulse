<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

interface QueryRepository
{
    public function getRepositoriesQuery(): string;

    public function getUpdatedPullRequestsQuery(): string;

    public function getPullRequestQuery(): string;

    public function getActivitiesQuery(): string;
}
