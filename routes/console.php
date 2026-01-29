<?php

declare(strict_types=1);

use App\Models\Repository;
use App\Services\GitProviders\GitProviderFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(static function (GitProviderFactory $factory): void {
    $condition = 'UNIX_TIMESTAMP(COALESCE(last_synced_at, statistics_from)) + sync_interval < ?';
    $query = Repository::whereRaw($condition, [time()]);

    /** @var Repository $repository */
    foreach ($query->lazy(50) as $repository) {
        $provider = $factory->create($repository->vcsInstance);
        Log::info("Syncing repository $repository->name");
        $provider->syncRepository($repository);
        Log::info("Repository $repository->name was successfully synced");
    }
})->everyMinute()->name('repository-sync')->withoutOverlapping();
