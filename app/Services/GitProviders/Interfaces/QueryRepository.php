<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

interface QueryRepository
{
    public function getRepositoriesQuery(): string;
}
