<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Services;

use Hekal\GuardRail\Exceptions\GuardRailException;
use Hekal\GuardRail\Models\Ability;
use Hekal\GuardRail\Models\ModelRole;
use Hekal\GuardRail\Models\Role;
use Hekal\GuardRail\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class AccessManager
{
    public function createTeam(string $name, ?string $slug = null): Team
    {
        return Team::query()->create([
            'name' => $name,
            'slug' => $slug ?? Str::slug($name),
        ]);
    }

    public function createAbility(string $name, ?string $slug = null): Ability
    {
        return Ability::query()->create([
            'name' => $name,
            'slug' => $slug ?? Str::slug($name, '.'),
        ]);
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
        $teamId = $team === null ? null : $this->resolveTeam($team)->id;

        $role = Role::query()->create([
            'team_id' => $teamId,
            'name' => $name,
            'slug' => $slug ?? Str::slug($name),
        ]);

        if ($abilities !== []) {
            $this->syncAbilities($role, $abilities);
        }

        return $role->load('abilities');
    }

    /**
     * @param  list<string|Ability>  $abilities
     */
    public function syncAbilities(Role $role, array $abilities): void
    {
        $ids = [];
        foreach ($abilities as $ability) {
            $ids[] = $this->resolveAbility($ability)->id;
        }

        $role->abilities()->sync($ids);
    }

    public function assignRole(Model $model, Role|string $role, Team|string|null $team = null): ModelRole
    {
        $resolvedRole = $this->resolveRole($role, $team);
        $teamId = $team === null ? $resolvedRole->team_id : $this->resolveTeam($team)->id;

        return ModelRole::query()->firstOrCreate([
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'role_id' => $resolvedRole->id,
            'team_id' => $teamId,
        ]);
    }

    public function removeRole(Model $model, Role|string $role, Team|string|null $team = null): void
    {
        $resolvedRole = $this->resolveRole($role, $team);
        $teamId = $team === null ? $resolvedRole->team_id : $this->resolveTeam($team)->id;

        ModelRole::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->where('role_id', $resolvedRole->id)
            ->where(function ($query) use ($teamId): void {
                if ($teamId === null) {
                    $query->whereNull('team_id');
                } else {
                    $query->where('team_id', $teamId);
                }
            })
            ->delete();
    }

    public function can(Model $model, string $ability, Team|string|null $team = null): bool
    {
        $teamId = $team === null ? null : $this->resolveTeam($team)->id;

        $query = ModelRole::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->whereHas('role.abilities', function ($q) use ($ability): void {
                $q->where('slug', $ability);
            });

        if ($teamId === null) {
            // Global check: any assignment (global or team) that grants the ability.
            return $query->exists();
        }

        return $query
            ->where(function ($q) use ($teamId): void {
                $q->where('team_id', $teamId)->orWhereNull('team_id');
            })
            ->exists();
    }

    private function resolveTeam(Team|string $team): Team
    {
        if ($team instanceof Team) {
            return $team;
        }

        $found = Team::query()->where('slug', $team)->first();
        if (! $found instanceof Team) {
            throw GuardRailException::teamNotFound($team);
        }

        return $found;
    }

    private function resolveAbility(Ability|string $ability): Ability
    {
        if ($ability instanceof Ability) {
            return $ability;
        }

        $found = Ability::query()->where('slug', $ability)->first();
        if (! $found instanceof Ability) {
            // Auto-create for convenience when syncing by slug string in tests/apps.
            return $this->createAbility($ability, $ability);
        }

        return $found;
    }

    private function resolveRole(Role|string $role, Team|string|null $team = null): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        $query = Role::query()->where('slug', $role);
        if ($team !== null) {
            $query->where('team_id', $this->resolveTeam($team)->id);
        }

        $found = $query->first();
        if (! $found instanceof Role) {
            throw GuardRailException::roleNotFound($role);
        }

        return $found;
    }
}
