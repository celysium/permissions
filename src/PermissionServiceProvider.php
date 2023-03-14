<?php

namespace Celysium\Permission;

use Celysium\Permission\Middleware\CheckPermission;
use Celysium\Permission\Middleware\CheckRole;
use Celysium\Permission\Models\Permission;
use Celysium\Permission\Models\Role;
use Celysium\Permission\Observers\PermissionObserver;
use Celysium\Permission\Observers\RoleObserver;
use Celysium\Permission\Traits\AuthorizesUser;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutes();

        $this->publishConfig();

        $this->loadMigrations();

        $this->registerMiddlewares();

        $this->registerGates();
    }

    public function register()
    {
        $this->registerConfig();

        $this->registerObservers();
    }

    public function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');
    }

    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function registerMiddlewares()
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
        $router->aliasMiddleware('role', CheckRole::class);
    }

    public function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/permission.php', 'permission'
        );
    }

    protected function registerGates()
    {
        Gate::define('role', function ($user, $role) {

            /** @var AuthorizesUser $user */
            return $user->hasRoles(explode('|', $role));
        });

        Gate::define('permission', function ($user, $permission) {

            /** @var AuthorizesUser $user */
            return $user->hasPermissions(explode('|', $permission));
        });
    }

    public function registerObservers()
    {
        $this->booting(function () {
            Permission::observe([PermissionObserver::class]);
            Role::observe([RoleObserver::class]);
        });
    }
}
