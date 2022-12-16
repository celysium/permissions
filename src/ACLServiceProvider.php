<?php

namespace Celysium\ACL;

use Illuminate\Support\ServiceProvider;

class ACLServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'acl-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/acl.php', 'acl'
        );

        $this->app->register(AclEventServiceProvider::class);
    }
}
