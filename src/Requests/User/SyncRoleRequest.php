<?php

namespace Celysium\Permission\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SyncRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
