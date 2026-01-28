<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\VcsInstanceUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => [Rule::enum(UserRole::class)],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($request->route()->parameter('id')),
            ],
            'vcs_instance_users' => ['array'],
            'vcs_instance_users.*' => [
                'integer',
                'distinct',
                Rule::exists(VcsInstanceUser::class, 'id'),
            ],
        ];

        if ($request->request->get('password') !== null || $request->request->get('password_confirmation') !== null) {
            $rules['password'] = [Password::defaults(), 'confirmed'];
        }

        return $rules;
    }
}
