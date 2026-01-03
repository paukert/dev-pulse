<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepositoryController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->query->getInt('per_page', 20);

        return Inertia::render('repositories/Repositories', [
            'repositories' => Repository::paginate($perPage)->through(static fn (Repository $repository): array => [
                'id' => $repository->id,
                'name' => $repository->name,
                'sync_interval' => $repository->sync_interval / (60 * 60),
                'statistics_from' => $repository->statistics_from,
                'last_synced_at' => $repository->last_synced_at,
            ]),
        ]);
    }
}
