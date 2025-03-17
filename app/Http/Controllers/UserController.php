<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->query->getInt('per_page', 20);

        return Inertia::render('users/Users', [
            'users' => User::paginate($perPage)->through(static fn(User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->query->getInt('id'));
        $user->delete();

        return to_route('users.index');
    }
}
