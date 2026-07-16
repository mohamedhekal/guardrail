<?php

declare(strict_types=1);

namespace Hekal\GuardRail;

use Hekal\GuardRail\Http\Middleware\EnsureAbility;
use Hekal\GuardRail\Services\ImpersonationService;
use Illuminate\Contracts\Session\Session;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class GuardRailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/guardrail.php', 'guardrail');

        $this->app->singleton(ImpersonationService::class, function ($app): ImpersonationService {
            return new ImpersonationService($app->make(Session::class));
        });

        $this->app->singleton(GuardRailManager::class);
        $this->app->alias(GuardRailManager::class, 'guardrail');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('guardrail.ability', EnsureAbility::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/guardrail.php' => config_path('guardrail.php'),
            ], 'guardrail-config');
        }
    }
}
