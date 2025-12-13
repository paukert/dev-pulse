<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reviewer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reviewer>
 */
class ReviewerFactory extends Factory
{
    protected $model = Reviewer::class;

    public function definition(): array
    {
        return [
            'assigned_at' => Carbon::now(),
        ];
    }
}
