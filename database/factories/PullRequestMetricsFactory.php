<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PullRequestMetrics;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PullRequestMetrics>
 */
class PullRequestMetricsFactory extends Factory
{
    protected $model = PullRequestMetrics::class;

    public function definition(): array
    {
        return [
            'added_lines' => fake()->randomNumber(3),
            'deleted_lines' => fake()->randomNumber(3),
            'files_count' => fake()->numberBetween(1, 20),
        ];
    }
}
