<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ChallengeActivityType;
use App\Http\Requests\Challenges\ChallengeCreateRequest;
use App\Models\Challenge;
use App\Models\ChallengeActivity;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->query->getInt('per_page', 20);

        $select = <<<SQL
            CASE
                WHEN badges.id IS NOT NULL THEN 'completed'
                WHEN challenges.active_to < NOW() THEN 'expired'
                WHEN challenges.active_from > NOW() THEN 'upcoming'
                ELSE 'active'
            END AS state
        SQL;

        $challengeQuery = Challenge::query()
            ->select(['challenges.*', DB::raw($select)])
            ->leftJoin('badges', function (JoinClause $join) use ($request): void {
                $join->on('challenges.id', '=', 'badges.challenge_id');
                $join->where('badges.user_id', '=', $request->user()->id);
            })
            ->orderBy('challenges.id', 'DESC');

        return Inertia::render('challenges/Challenges', [
            'challenges' => $challengeQuery->paginate($perPage),
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
