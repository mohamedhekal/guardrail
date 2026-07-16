<?php

declare(strict_types=1);

namespace Hekal\GuardRail;

use Hekal\GuardRail\Models\Ability;
use Hekal\GuardRail\Models\ImpersonationLog;
use Hekal\GuardRail\Models\ModelRole;
use Hekal\GuardRail\Models\Role;
use Hekal\GuardRail\Models\Team;
use Hekal\GuardRail\Services\AccessManager;
use Hekal\GuardRail\Services\ImpersonationService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class GuardRailManager
{
    public function __construct(
        private readonly AccessManager $access,
        private readonly ImpersonationService $impersonation,
    ) {}

    public function createTeam(string $name, ?string $slug = null): Team
    {
        return $this->access->createTeam($name, $slug);
    }

    public function createAbility(string $name, ?string $slug = null): Ability
    {
        return $this->access->createAbility($name, $slug);
    }

    /**
     * @param  list<string|Ability>  $abilities
     */
    public function createRole(
        string $name,
        Team|string|null $team = null,
        array $abilities = [],
        ?string $slug = null,
    ): Role {
        return $this->access->createRole($name, $team, $abilities, $slug);
    }

    public function assignRole(Model $model, Role|string $role, Team|string|null $team = null): ModelRole
    {
        return $this->access->assignRole($model, $role, $team);
    }

    public function removeRole(Model $model, Role|string $role, Team|string|null $team = null): void
    {
        $this->access->removeRole($model, $role, $team);
    }

    public function can(Model $model, string $ability, Team|string|null $team = null): bool
    {
        return $this->access->can($model, $ability, $team);
    }

    public function cannot(Model $model, string $ability, Team|string|null $team = null): bool
    {
        return ! $this->can($model, $ability, $team);
    }

    public function startImpersonation(
        Authenticatable&Model $actor,
        Authenticatable&Model $target,
        string $reason,
        Team|string|null $team = null,
        ?int $ttlMinutes = null,
    ): ImpersonationLog {
        return $this->impersonation->start($actor, $target, $reason, $team, $ttlMinutes);
    }

    public function stopImpersonation(): ImpersonationLog
    {
        return $this->impersonation->stop();
    }

    public function isImpersonating(): bool
    {
        return $this->impersonation->isImpersonating();
    }
}
