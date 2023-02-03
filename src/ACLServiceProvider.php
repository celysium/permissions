<?php

namespace Celysium\ACL;

use Celysium\ACL\Middleware\CheckPermission;
use Celysium\ACL\Middleware\CheckRole;
use Celysium\ACL\Models\Permission;
use Celysium\ACL\Models\Role;
use Celysium\ACL\Observers\PermissionObserver;
use Celysium\ACL\Observers\RoleObserver;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ACLServiceProvider extends ServiceProvider
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
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'acl-config');
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
            __DIR__ . '/../config/acl.php', 'acl'
        );
    }

    protected function registerGates()
    {
        Gate::define('role', function ($user, $role) {
            $roles = explode('|', $role);

            return $user->hasRolesCache($roles);
        });

        Gate::define('permission', function ($user, $permission) {
            $permissions = explode('|', $permission);

            return $user->hasPermissionsCache($permissions);
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
