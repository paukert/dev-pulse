<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Repository\RepositoryCreateRequest;
use App\Http\Requests\Repository\RepositoryUpdateRequest;
use App\Models\Repository;
use App\Models\VcsInstance;
use App\Services\GitProviders\GitProviderFactory;
use Illuminate\Http\JsonResponse;
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

    public function create(): Response
    {
        return Inertia::render('repositories/Create', [
            'vcs_instances' => VcsInstance::all()->map(static fn (VcsInstance $vcsInstance): array => [
                'id' => $vcsInstance->id,
                'name' => $vcsInstance->name,
            ])
        ]);
    }

    public function store(RepositoryCreateRequest $request): RedirectResponse
    {
        $validated = $request->safe();

        $repository = new Repository($validated->except(['sync_interval_hours']));
        $repository->sync_interval = $validated['sync_interval_hours'] * (60 * 60);
        $repository->save();

        return to_route('repositories.index');
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

    public function search(Request $request, GitProviderFactory $factory): JsonResponse
    {
        $vcsInstance = VcsInstance::findOrFail($request->query->getInt('vcs_instance_id'));

        $data = $factory->create($vcsInstance)->getAvailableRepositories($request->query->getString('query'));
        $repositories = array_map(
            static fn (array $repository): array => [
                'value' => $repository['vcsId'],
                'label' => $repository['name'],
            ],
            $data->items
        );

        return response()->json($repositories);
    }
}
