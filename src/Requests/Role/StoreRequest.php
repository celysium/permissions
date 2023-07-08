<?php

namespace Celysium\Permission\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:193', 'unique:permissions,name'],
            'title'   => ['required', 'string', 'max:193', 'unique:permissions,title'],
            'status'  => ['required', 'boolean'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
