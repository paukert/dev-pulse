<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        return [
            'vcs_id' => fake()->uuid(),
            'resolved_at' => Carbon::now(),
        ];
    }
}
