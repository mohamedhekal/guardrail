<?php

declare(strict_types=1);

use Hekal\GuardRail\Facades\GuardRail;
use Hekal\GuardRail\Http\Middleware\EnsureAbility;
use Hekal\GuardRail\Testing\User;
use Illuminate\Support\Facades\Route;

it('grants abilities through team-scoped roles', function () {
    $acme = GuardRail::createTeam('Acme', 'acme');
    $beta = GuardRail::createTeam('Beta', 'beta');

    GuardRail::createAbility('Edit orders', 'orders.edit');
    GuardRail::createRole('Editor', $acme, ['orders.edit'], 'editor');
    GuardRail::createRole('Viewer', $beta, [], 'viewer');

    $user = User::query()->create(['name' => 'Ada', 'email' => 'ada@example.com']);
    GuardRail::assignRole($user, 'editor', $acme);

    expect(GuardRail::can($user, 'orders.edit', $acme))->toBeTrue()
        ->and(GuardRail::can($user, 'orders.edit', $beta))->toBeFalse()
        ->and($user->hasAbility('orders.edit', $acme))->toBeTrue();
});

it('removes roles and revokes abilities', function () {
    $team = GuardRail::createTeam('Solo', 'solo');
    GuardRail::createAbility('Delete', 'orders.delete');
    GuardRail::createRole('Admin', $team, ['orders.delete'], 'admin');

    $user = User::query()->create(['name' => 'Bob', 'email' => 'bob@example.com']);
    GuardRail::assignRole($user, 'admin', $team);
    expect(GuardRail::can($user, 'orders.delete', $team))->toBeTrue();

    GuardRail::removeRole($user, 'admin', $team);
    expect(GuardRail::cannot($user, 'orders.delete', $team))->toBeTrue();
});

it('records impersonation with reason and session markers', function () {
    $actor = User::query()->create(['name' => 'Support', 'email' => 'support@example.com']);
    $target = User::query()->create(['name' => 'Customer', 'email' => 'customer@example.com']);

    $log = GuardRail::startImpersonation($actor, $target, reason: 'ticket #99', ttlMinutes: 15);

    expect(GuardRail::isImpersonating())->toBeTrue()
        ->and($log->reason)->toBe('ticket #99')
        ->and($log->isActive())->toBeTrue();

    $stopped = GuardRail::stopImpersonation();
    expect($stopped->ended_at)->not->toBeNull()
        ->and(GuardRail::isImpersonating())->toBeFalse();
});

it('blocks routes without the required ability', function () {
    $team = GuardRail::createTeam('Web', 'web');
    GuardRail::createAbility('Billing', 'billing.manage');
    GuardRail::createRole('Billing', $team, ['billing.manage'], 'billing');

    $allowed = User::query()->create(['name' => 'Ok', 'email' => 'ok@example.com']);
    $denied = User::query()->create(['name' => 'No', 'email' => 'no@example.com']);
    GuardRail::assignRole($allowed, 'billing', $team);

    Route::middleware(['web', EnsureAbility::class.':billing.manage,web'])
        ->get('/billing', fn () => 'secret');

    $this->actingAs($denied)->get('/billing')->assertForbidden();
    $this->actingAs($allowed)->get('/billing')->assertOk()->assertSee('secret');
});
