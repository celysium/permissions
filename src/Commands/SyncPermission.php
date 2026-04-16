<?php

namespace Celysium\Permission\Commands;

use Celysium\Permission\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class SyncPermission extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync to permission';

    public function handle()
    {
        $permissions = $this->dot(config('permission.permissions'));
        foreach ($permissions as $name) {
            $permission = Permission::query()->createOrFirst([
                "name" => $name
            ]);

            $this->info("Permission `{$name}` " . ($permission->wasRecentlyCreated ? 'created' : 'already exists'));
        }
    }

    public function dot(array $permissions, string $prefix = ''): array
    {
        $results = [];

        foreach ($permissions as $key => $value) {

            $prepend = $prefix.$key;
            if (is_array($value)) {
                $this->dot($value, $prepend.'.');
            } else {
                $results[] = $prepend.$value;
            }
        }

        return array_unique($results);
    }
}
