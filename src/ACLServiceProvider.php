<?php

namespace Celysium\ACL;

use App\Http\Kernel;
use Celysium\ACL\Middleware\CheckPermission;
use Celysium\ACL\Middleware\CheckRole;
use Celysium\ACL\Models\Permission;
use Celysium\ACL\Models\Role;
use Celysium\ACL\Observers\PermissionObserver;
use Celysium\ACL\Observers\RoleObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ACLServiceProvider extends ServiceProvider
{
    public function boot(Kernel $kernel)
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'acl-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $kernel->pushMiddleware(CheckPermission::class);
        $kernel->pushMiddleware(CheckRole::class);

        $this->registerGates();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/acl.php', 'acl'
        );

        $this->booting(function () {
            Permission::observe([PermissionObserver::class]);
            Role::observe([RoleObserver::class]);
        });
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
}
