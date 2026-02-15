<?php

declare(strict_types=1);

namespace App\Http\Requests\VcsInstances;

use App\Models\VcsInstance;
use Illuminate\Foundation\Http\FormRequest;

class VcsInstanceCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return VcsInstance::rules();
    }
}
