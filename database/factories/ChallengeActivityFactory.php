<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ChallengeActivityType;
use App\Models\ChallengeActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChallengeActivity>
 */
class ChallengeActivityFactory extends Factory
{
    protected $model = ChallengeActivity::class;

    public function definition(): array
    {
        return [
            'activity_type' => fake()->randomElement(ChallengeActivityType::cases()),
            'needed_actions_count' => $this->faker->randomDigitNotZero(),
        ];
    }
}
