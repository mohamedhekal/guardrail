<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Events;

use Hekal\GuardRail\Models\ImpersonationLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ImpersonationStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ImpersonationLog $log,
    ) {}
}
