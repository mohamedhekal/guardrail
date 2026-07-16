<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Http\Middleware;

use Closure;
use Hekal\GuardRail\Facades\GuardRail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAbility
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $ability, ?string $team = null): Response
    {
        $user = $request->user();
        if ($user === null || ! GuardRail::can($user, $ability, $team)) {
            abort(403, "Missing ability [{$ability}].");
        }

        return $next($request);
    }
}
