<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Repository;
use App\Models\VcsInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    protected $model = Repository::class;

    public function definition(): array
    {
        return [
            'vcs_id' => fake()->uuid(),
            'name' => fake()->domainWord(),
            'sync_interval' => fake()->randomDigitNotZero() * 3600,
            'statistics_from' => fake()->dateTimeBetween('-3 months', 'now'),
            'vcs_instance_id' => VcsInstance::query()->inRandomOrder()->first()->id,
        ];
    }
}
