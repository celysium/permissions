<?php

namespace Celysium\Permission\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:193', 'unique:permissions,name,' . $this->route('role')?->id],
            'title'         => ['required', 'string', 'max:193', 'unique:permissions,title,' . $this->route('role')?->id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
