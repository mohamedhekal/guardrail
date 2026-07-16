<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $team_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $team
 */
final class Role extends Model
{
    protected $table = 'gr_roles';

    protected $fillable = ['team_id', 'name', 'slug'];

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsToMany<Ability, $this>
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'gr_ability_role');
    }
}
