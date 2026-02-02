<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\User;
use App\Models\VcsInstanceUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'role' => $user->role->getLabel(),
            ]),
        ]);
    }

    public function edit(int $id): Response
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        return Inertia::render('users/Edit', [
            'user' => $user->only(['id', 'name', 'role', 'email']),
            'vcsInstanceUsers' => $user->vcsInstanceUsers->map(static fn (VcsInstanceUser $vcsInstanceUser): array => [
                'value' => $vcsInstanceUser->id,
                'label' => "{$vcsInstanceUser->vcsInstance->name} / $vcsInstanceUser->username",
                'badge' => $vcsInstanceUser->vcsInstance->platform->getLabel(),
            ]),
            'roles' => UserRole::getLabels(),
        ]);
    }

    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        $validated = $request->safe();
        $user->fill($validated->except(['role', 'vcs_instance_users']));
        $user->role = $validated['role'] ?? $user->role;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        DB::transaction(static function () use ($user, $validated): void {
            VcsInstanceUser::query()
                ->where('user_id', '=', $user->id)
                ->update(['user_id' => null]);

            if (count($validated['vcs_instance_users'] ?? []) !== 0) {
                VcsInstanceUser::query()
                    ->whereIn('id', $validated['vcs_instance_users'])
                    ->update(['user_id' => $user->id]);
            }
        });

        return to_route('users.edit', ['id' => $id]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->query->getInt('id'));
        $user->delete();

        return to_route('users.index');
    }

    public function search(Request $request): JsonResponse
    {
        $users = DB::table('users')
            ->select(['id AS value', 'name AS label'])
            ->whereLike('name', "%{$request->query->getString('query')}%")
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
