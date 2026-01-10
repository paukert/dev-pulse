<?php

declare(strict_types=1);

namespace App\Http\Requests\Repository;

use App\Models\Repository;
use Illuminate\Foundation\Http\FormRequest;

class RepositoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return collect(Repository::rules())->only(['name', 'sync_interval_hours'])->toArray();
    }
}
