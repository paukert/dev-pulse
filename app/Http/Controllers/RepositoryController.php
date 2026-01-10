<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Repository\RepositoryUpdateRequest;
use App\Models\Repository;
use Illuminate\Http\RedirectResponse;
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
                'sync_interval_hours' => $repository->sync_interval / (60 * 60),
                'statistics_from' => $repository->statistics_from,
                'last_synced_at' => $repository->last_synced_at,
            ]),
        ]);
    }

    public function edit(Repository $repository): Response
    {
        return Inertia::render('repositories/Edit', [
            'repository' => [
                'id' => $repository->id,
                'name' => $repository->name,
                'sync_interval_hours' => $repository->sync_interval / (60 * 60),
            ],
        ]);
    }

    public function update(RepositoryUpdateRequest $request, Repository $repository): RedirectResponse
    {
        $validated = $request->safe();
        $repository->fill($validated->only(['name']));
        $repository->sync_interval = $validated->has('sync_interval_hours')
            ? $validated['sync_interval_hours'] * (60 * 60)
            : $repository->sync_interval;

        $repository->save();

        return to_route('repositories.edit', ['repository' => $repository->id]);
    }

    public function destroy(Repository $repository): RedirectResponse
    {
        $repository->delete();

        return to_route('repositories.index');
    }
}
