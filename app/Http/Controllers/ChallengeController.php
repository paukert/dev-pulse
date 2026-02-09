<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Challenge;
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

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return to_route('challenges.index');
    }
}
