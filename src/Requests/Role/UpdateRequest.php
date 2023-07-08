<?php

namespace Celysium\Permission\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:193', 'unique:permissions,name,' . $this->route('permission')->id],
            'title'   => ['required', 'string', 'max:193', 'unique:permissions,title,' . $this->route('permission')->id],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
