<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $actor_user_id
 * @property int $target_user_id
 * @property int|null $team_id
 * @property string $reason
 * @property Carbon $started_at
 * @property Carbon $ends_at
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $team
 */
final class ImpersonationLog extends Model
{
    protected $table = 'gr_impersonation_logs';

    protected $fillable = [
        'actor_user_id',
        'target_user_id',
        'team_id',
        'reason',
        'started_at',
        'ends_at',
        'ended_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function isActive(): bool
    {
        return $this->ended_at === null && $this->ends_at->isFuture();
    }
}
