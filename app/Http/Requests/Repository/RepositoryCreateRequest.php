<?php

declare(strict_types=1);

namespace App\Http\Requests\Repository;

use App\Models\Repository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class RepositoryCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        return collect(Repository::rules($request))->only([
            'vcs_id',
            'name',
            'sync_interval_hours',
            'statistics_from',
            'vcs_instance_id',
        ])->toArray();
    }
}
