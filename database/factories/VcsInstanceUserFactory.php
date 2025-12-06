<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VcsInstanceUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VcsInstanceUser>
 */
class VcsInstanceUserFactory extends Factory
{
    protected $model = VcsInstanceUser::class;

    public function definition(): array
    {
        return [
            'vcs_id' => fake()->uuid(),
            'username' => fake()->userName(),
        ];
    }
}
