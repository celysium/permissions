<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('permission')) {
    function permission(string $checkType, array $data, $user = null): bool
    {
        if (is_null($user))
            $user = Auth::user();

        if ($checkType === 'roles') {
            return $user->hasRolesCache($data);
        } elseif ($checkType === 'permissions') {
            return $user->hasPermissionsCache($data);
        }
        return false;
    }
}

