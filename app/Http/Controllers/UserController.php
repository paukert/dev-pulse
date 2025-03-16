<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('users/Users', [
            'users' => User::paginate()->through(static fn(User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]),
        ]);
    }
}
