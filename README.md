# GuardRail

Team-scoped roles, abilities, and audited impersonation for Laravel SaaS—focused on the patterns Spatie-style packages often leave to the app.

## Installation

```bash
composer require hekal/guardrail
php artisan vendor:publish --tag=guardrail-config
php artisan migrate
```

## Setup

```php
use Hekal\GuardRail\Concerns\HasGuardRail;

class User extends Authenticatable
{
    use HasGuardRail;
}
```

## Roles & abilities

```php
use Hekal\GuardRail\Facades\GuardRail;

$team = GuardRail::createTeam('Acme', 'acme');
GuardRail::createAbility('Edit orders', 'orders.edit');
GuardRail::createRole('Editor', $team, ['orders.edit'], 'editor');

GuardRail::assignRole($user, 'editor', $team);
GuardRail::can($user, 'orders.edit', $team); // true
```

Middleware:

```php
Route::middleware('guardrail.ability:orders.edit,acme')->group(...);
```

## Impersonation

```php
GuardRail::startImpersonation($admin, $customer, reason: 'ticket #12', ttlMinutes: 30);
// switch auth in the host app using the target user
GuardRail::stopImpersonation(); // writes ended_at + clears session markers
```

## Limitations (v0.1)

- No ABAC / policy codegen
- Global ability check (no team) matches any team assignment that grants it
- Host app must perform the actual Auth::login during impersonation

## Testing

```bash
composer install && composer test
```

## License

MIT
