<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $role = UserRole::tryFrom($role) ?? throw new \InvalidArgumentException('Invalid role');

        if (!$request->user()->hasRole($role)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
