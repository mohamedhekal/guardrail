<?php

declare(strict_types=1);

namespace Hekal\GuardRail\Testing;

use Hekal\GuardRail\Concerns\HasGuardRail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Minimal authenticatable user for package tests and demos.
 */
final class User extends Authenticatable
{
    use HasGuardRail;

    protected $table = 'users';

    protected $guarded = [];
}
