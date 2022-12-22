<?php

namespace Celysium\ACL;

use Celysium\ACL\Models\Permission;
use Celysium\ACL\Models\Role;
use Celysium\ACL\Observers\PermissionObserver;
use Celysium\ACL\Observers\RoleObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ACLServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'acl-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

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

            if (config('acl.shop_mode') === 'enterprise') {
                //
            }

            return $user->hasPermissionsCache($permissions);
        });
    }
}
