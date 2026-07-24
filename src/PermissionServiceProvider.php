<?php

namespace Celysium\Permission;

use Celysium\Permission\Commands\CreatePermission;
use Celysium\Permission\Commands\CreateRole;
use Celysium\Permission\Commands\SyncPermission;
use Celysium\Permission\Middleware\CheckPermission;
use Celysium\Permission\Middleware\CheckRole;
use Celysium\Permission\Models\Permission;
use Celysium\Permission\Models\Role;
use Celysium\Permission\Observers\PermissionObserver;
use Celysium\Permission\Observers\RoleObserver;
use Celysium\Permission\Traits\Permissions;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishConfig();

        $this->publishMigrations();

        $this->registerMiddlewares();

        $this->registerGates();

        $this->registerConfig();

        $this->registerCommands();
    }

    public function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');
    }

    public function publishMigrations(): void
    {
        $this->publishesMigrations([
            __DIR__ . '/../database/migrations', database_path('migrations')
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function registerMiddlewares(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
        $router->aliasMiddleware('role', CheckRole::class);
    }

    public function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/permission.php', 'permission'
        );

    }

    protected function registerGates(): void
    {
        Gate::define('role', function ($user, string $role) {
            /** @var Permissions $user */
            return $user->hasRoles($role);
        });

        Gate::define('permission', function ($user, string $permission) {
            /** @var Permissions $user */
            return (bool) count($user->getSubPermissions($permission));
        });
    }

    public function registerCommands(): void
    {
        $this->commands([
            SyncPermission::class,
        ]);
    }
}
