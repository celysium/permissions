<?php

namespace Celysium\Permission\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:193', 'unique:permissions,name'],
            'title'         => ['required', 'string', 'max:193', 'unique:permissions,title'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
