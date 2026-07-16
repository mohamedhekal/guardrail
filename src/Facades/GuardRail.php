<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Facades;

use Hekal\GuardRail\GuardRailManager;
use Hekal\GuardRail\Models\Ability;
use Hekal\GuardRail\Models\ImpersonationLog;
use Hekal\GuardRail\Models\ModelRole;
use Hekal\GuardRail\Models\Role;
use Hekal\GuardRail\Models\Team;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Team createTeam(string $name, ?string $slug = null)
 * @method static Ability createAbility(string $name, ?string $slug = null)
 * @method static Role createRole(string $name, Team|string|null $team = null, array<int, string|Ability> $abilities = [], ?string $slug = null)
 * @method static ModelRole assignRole(Model $model, Role|string $role, Team|string|null $team = null)
 * @method static void removeRole(Model $model, Role|string $role, Team|string|null $team = null)
 * @method static bool can(Model $model, string $ability, Team|string|null $team = null)
 * @method static bool cannot(Model $model, string $ability, Team|string|null $team = null)
 * @method static ImpersonationLog startImpersonation(Authenticatable&Model $actor, Authenticatable&Model $target, string $reason, Team|string|null $team = null, ?int $ttlMinutes = null)
 * @method static ImpersonationLog stopImpersonation()
 * @method static bool isImpersonating()
 *
 * @see GuardRailManager
 */
final class GuardRail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GuardRailManager::class;
    }
}
