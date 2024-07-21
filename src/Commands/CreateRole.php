<?php

namespace Celysium\Permission\Commands;

use Celysium\Permission\Models\Role;
use Illuminate\Console\Command;

class CreateRole extends Command
{
    protected $signature = 'permissions:create-role 
                {name : The name of the role}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $role = Role::query()->createOrFirst([
            "name" => $this->argument("name")
        ]);

        $this->info("Role `{$role->name}` " . ($role->wasRecentlyCreated ? 'created' : 'already exists'));
    }
}
