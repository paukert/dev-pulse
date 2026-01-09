<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'active_from' => Carbon::now()->subDays(fake()->numberBetween(10, 20)),
            'active_to' => Carbon::now()->addDays(fake()->numberBetween(5, 10)),
        ];
    }
}
