<?php

if (! function_exists('checkUserPermissions')) {
    function checkUserPermissions($user, array $permissions): bool
    {
        return $user->hasPermissions($permissions);
    }
}

if (! function_exists('checkUserRoles')) {
    function checkUserRoles($user, array $roles): bool
    {
        return $user->hasRoles($roles);
    }
}
