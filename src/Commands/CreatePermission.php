<?php

namespace Celysium\Permission\Commands;

use Celysium\Permission\Models\Permission;
use Illuminate\Console\Command;

class CreatePermission extends Command
{
    protected $signature = 'permissions:create-permission 
                {name : The name of the permission}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $permission = Permission::query()->createOrFirst([
            "name" => $this->argument("name")
        ]);

        $this->info("Permission `{$permission->name}` " . ($permission->wasRecentlyCreated ? 'created' : 'already exists'));
    }
}
