<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Exceptions;

use RuntimeException;

final class GuardRailException extends RuntimeException
{
    public static function roleNotFound(string $slug): self
    {
        return new self("Role [{$slug}] was not found.");
    }

    public static function abilityNotFound(string $slug): self
    {
        return new self("Ability [{$slug}] was not found.");
    }

    public static function teamNotFound(string $slug): self
    {
        return new self("Team [{$slug}] was not found.");
    }

    public static function impersonationExpired(): self
    {
        return new self('Impersonation session has expired.');
    }

    public static function notImpersonating(): self
    {
        return new self('No active impersonation session.');
    }
}
