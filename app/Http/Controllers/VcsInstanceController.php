<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VcsPlatform;
use App\Http\Requests\VcsInstances\VcsInstanceCreateRequest;
use App\Models\VcsInstance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VcsInstanceController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('vcsInstances/Create', [
            'platforms' => VcsPlatform::getLabels(),
        ]);
    }

    public function store(VcsInstanceCreateRequest $request): RedirectResponse
    {
        $vcsInstance = new VcsInstance($request->validated());
        $vcsInstance->installation_id = $vcsInstance->platform === VcsPlatform::GITHUB ? $vcsInstance->installation_id : null;
        $vcsInstance->token = $vcsInstance->platform === VcsPlatform::GITLAB ? $vcsInstance->token : null;
        $vcsInstance->save();

        return to_route('repositories.index');
    }
}
