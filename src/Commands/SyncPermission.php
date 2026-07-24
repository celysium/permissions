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
            $permission = Permission::query()->firstOrCreate([
                "name" => $name
            ]);

            if($permission->wasRecentlyCreated) {
                $this->info("Permission `{$name}` created");
            }
        }
    }

    public function dot(array $permissions, string $prefix = ''): array
    {
        $results = [];

        foreach ($permissions as $key => $value) {

            if (is_array($value)) {
                $results = array_merge($results, $this->dot($value, $prefix.$key.'.'));
            } else {
                $results[] = $prefix.$value;
            }
        }

        return array_unique($results);
    }
}
