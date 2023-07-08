<?php

namespace Celysium\Permission\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'permissions'           => ['required', 'array'],
            'permissions.*.id'      => ['integer', 'exists:permissions,id'],
            'permissions.*.is_able' => ['required', 'boolean'],
        ];
    }
}
