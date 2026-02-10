<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ChallengeActivityType;
use App\Http\Requests\Challenges\ChallengeCreateRequest;
use App\Models\Challenge;
use App\Models\ChallengeActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->query->getInt('per_page', 20);

        return Inertia::render('challenges/Challenges', [
            'challenges' => Challenge::paginate($perPage),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('challenges/Create', [
            'supportedActivityTypes' => ChallengeActivityType::getLabels(),
        ]);
    }

    public function store(ChallengeCreateRequest $request): RedirectResponse
    {
        $validated = $request->safe();

        $challenge = new Challenge($validated->except(['activities']));
        $challenge->save();

        foreach ($validated['activities'] as $validatedActivity) {
            $activity = new ChallengeActivity();
            $activity->challenge_id = $challenge->id;
            $activity->activity_type = $validatedActivity['type'];
            $activity->needed_actions_count = $validatedActivity['needed_actions_count'];
            $activity->save();
        }

        // TODO: redirect to the challenge detail page in the future
        return to_route('challenges.index');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return to_route('challenges.index');
    }
}
