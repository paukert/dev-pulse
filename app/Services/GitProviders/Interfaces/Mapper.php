<?php

declare(strict_types=1);

namespace App\Services\GitProviders\Interfaces;

use App\DTOs\RepositoriesListDTO;

interface Mapper
{
    public function mapRepositoryCollection(array $data): RepositoriesListDTO;
}
