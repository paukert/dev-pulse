<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PullRequestState;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\VcsInstanceUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PullRequest>
 */
class PullRequestFactory extends Factory
{
    protected $model = PullRequest::class;

    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-2 months', '-1 week');
        $state = fake()->randomElement(PullRequestState::cases());
        $stateUpdatedAt = (clone $createdAt)->modify('+' . fake()->randomDigitNotZero() . ' days');
        $authorId = VcsInstanceUser::inRandomOrder()->first()->id;

        return [
            'vcs_id' => fake()->uuid(),
            'title' => 'TASK-' . fake()->randomNumber(4),
            'state' => $state,
            'created_at' => $createdAt,
            'updated_at' => $stateUpdatedAt,
            'merged_at' => $state === PullRequestState::MERGED ? $stateUpdatedAt : null,
            'closed_at' => $state === PullRequestState::CLOSED ? $stateUpdatedAt : null,
            'repository_id' => Repository::inRandomOrder()->first()->id,
            'author_id' => $authorId,
            'merged_by_user_id' => $state === PullRequestState::MERGED
                ? VcsInstanceUser::inRandomOrder()->firstWhere('id', '!=', $authorId)->id
                : null,
            'closed_by_user_id' => $state === PullRequestState::CLOSED
                ? VcsInstanceUser::inRandomOrder()->firstWhere('id', '!=', $authorId)->id
                : null,
        ];
    }
}
