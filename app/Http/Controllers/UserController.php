<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Users\UserUpdateRequest;
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
            'users' => User::paginate($perPage)->through(static fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]),
        ]);
    }

    public function edit(int $id): Response
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        return Inertia::render('users/Edit', [
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = User::findOrFail($id);
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('users.edit', ['id' => $id]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->query->getInt('id'));
        $user->delete();

        return to_route('users.index');
    }
}
