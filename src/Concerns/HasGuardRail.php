<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Concerns;

use Hekal\GuardRail\Facades\GuardRail;
use Hekal\GuardRail\Models\ModelRole;
use Hekal\GuardRail\Models\Role;
use Hekal\GuardRail\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 */
trait HasGuardRail
{
    /**
     * @return MorphMany<ModelRole, $this>
     */
    public function guardRailRoles(): MorphMany
    {
        return $this->morphMany(ModelRole::class, 'model');
    }

    public function assignRole(Role|string $role, Team|string|null $team = null): void
    {
        GuardRail::assignRole($this, $role, $team);
    }

    public function removeRole(Role|string $role, Team|string|null $team = null): void
    {
        GuardRail::removeRole($this, $role, $team);
    }

    public function hasAbility(string $ability, Team|string|null $team = null): bool
    {
        return GuardRail::can($this, $ability, $team);
    }
}
