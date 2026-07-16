<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Services;

use Hekal\GuardRail\Events\ImpersonationEnded;
use Hekal\GuardRail\Events\ImpersonationStarted;
use Hekal\GuardRail\Exceptions\GuardRailException;
use Hekal\GuardRail\Models\ImpersonationLog;
use Hekal\GuardRail\Models\Team;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Model;

final class ImpersonationService
{
    public function __construct(
        private readonly Session $session,
    ) {}

    public function start(
        Authenticatable&Model $actor,
        Authenticatable&Model $target,
        string $reason,
        Team|string|null $team = null,
        ?int $ttlMinutes = null,
    ): ImpersonationLog {
        $ttl = $ttlMinutes ?? (int) config('guardrail.impersonation.default_ttl_minutes', 30);
        $teamId = null;

        if ($team !== null) {
            $teamId = $team instanceof Team
                ? $team->id
                : Team::query()->where('slug', $team)->value('id');
        }

        $log = ImpersonationLog::query()->create([
            'actor_user_id' => (int) $actor->getAuthIdentifier(),
            'target_user_id' => (int) $target->getAuthIdentifier(),
            'team_id' => $teamId,
            'reason' => $reason,
            'started_at' => now(),
            'ends_at' => now()->addMinutes($ttl),
        ]);

        $this->session->put(
            (string) config('guardrail.impersonation.session_key', 'guardrail.impersonator_id'),
            $actor->getAuthIdentifier(),
        );
        $this->session->put('guardrail.impersonation_log_id', $log->id);

        event(new ImpersonationStarted($log));

        return $log;
    }

    public function stop(): ImpersonationLog
    {
        $logId = $this->session->get('guardrail.impersonation_log_id');
        if ($logId === null) {
            throw GuardRailException::notImpersonating();
        }

        $log = ImpersonationLog::query()->find($logId);
        if (! $log instanceof ImpersonationLog) {
            throw GuardRailException::notImpersonating();
        }

        if (! $log->isActive() && $log->ended_at === null) {
            throw GuardRailException::impersonationExpired();
        }

        $log->forceFill(['ended_at' => now()])->save();
        $this->session->forget([
            (string) config('guardrail.impersonation.session_key', 'guardrail.impersonator_id'),
            'guardrail.impersonation_log_id',
        ]);

        event(new ImpersonationEnded($log));

        return $log;
    }

    public function isImpersonating(): bool
    {
        return $this->session->has(
            (string) config('guardrail.impersonation.session_key', 'guardrail.impersonator_id')
        );
    }
}
