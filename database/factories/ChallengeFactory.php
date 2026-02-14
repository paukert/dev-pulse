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
        $activeFrom = Carbon::now()->subDays(fake()->numberBetween(-5, 20));

        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'active_from' => $activeFrom,
            'active_to' => (clone $activeFrom)->addDays(fake()->numberBetween(5, 10)),
        ];
    }
}
