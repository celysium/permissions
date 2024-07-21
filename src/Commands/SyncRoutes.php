<?php

namespace Celysium\Permission\Commands;

use Celysium\Permission\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class SyncRoutes extends Command
{
    protected $signature = 'permissions:sync-routes';

    protected $description = 'Sync routes to permission';

    public function handle()
    {
        foreach (Route::getRoutes() as $route) {
            if ($routeName = $route->getName()) {
                $permission = Permission::query()->createOrFirst([
                    "name" => $routeName
                ]);

                $this->info("Permission `{$routeName}` " . ($permission->wasRecentlyCreated ? 'created' : 'already exists'));
            } else {
                $this->warn("Route `{$route->uri()}` can't store because name is empty.");
            }
        }
    }
}
