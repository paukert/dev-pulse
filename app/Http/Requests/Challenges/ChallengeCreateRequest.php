<?php

declare(strict_types=1);

namespace App\Http\Requests\Challenges;

use App\Models\Challenge;
use Illuminate\Foundation\Http\FormRequest;

class ChallengeCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Challenge::rules();
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public function messages(): array
    {
        return Challenge::messages();
    }
}
