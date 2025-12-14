<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Approver;
use App\Models\Comment;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\Reviewer;
use App\Models\Thread;
use App\Models\User;
use App\Models\VcsInstance;
use App\Models\VcsInstanceUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(50)->create();
        VcsInstance::factory(4)
            ->create()
            ->each(static function (VcsInstance $vcsInstance): void {
                $users = User::inRandomOrder()->limit(20)->get();
                VcsInstanceUser::factory(20)
                    ->sequence(
                        ...$users->map(static fn (User $user): array => [
                            'user_id' => $user->id,
                            'vcs_instance_id' => $vcsInstance->id,
                        ])
                    )
                    ->create();
            });
        Repository::factory(20)->create();

        $pullRequests = PullRequest::factory(100)
            ->hasMetrics()
            ->create();

        /** @var PullRequest $pullRequest */
        foreach ($pullRequests as $pullRequest) {
            $reviewers = VcsInstanceUser::inRandomOrder()
                ->where('vcs_instance_id', '=', $pullRequest->repository->vcs_instance_id)
                ->where('id', '!=', $pullRequest->author_id)
                ->limit(rand(0, 2))
                ->get();

            /** @var VcsInstanceUser $reviewer */
            foreach ($reviewers as $reviewer) {
                $attrs = [
                    'assigned_at' => fake()->dateTimeBetween($pullRequest->created_at, $pullRequest->updated_at ?? now()),
                    'pull_request_id' => $pullRequest->id,
                    'vcs_instance_user_id' => $reviewer->id,
                ];

                Reviewer::factory(1, $attrs)->create();
            }

            $approvers = $pullRequest->reviewers->filter(static fn (): bool => fake()->boolean(75));

            /** @var Reviewer $approver */
            foreach ($approvers as $approver) {
                $attrs = [
                    'approved_at' => fake()->dateTimeBetween($approver->assigned_at, $pullRequest->updated_at ?? now()),
                    'pull_request_id' => $pullRequest->id,
                    'vcs_instance_user_id' => $approver->vcs_instance_user_id,
                ];

                Approver::factory(1, $attrs)->create();
            }

            $commenters = $reviewers->pluck('id')->merge([$pullRequest->author_id]);
            $attrsCallback = function () use ($commenters, $pullRequest): array {
                $resolved = fake()->boolean(75);

                return [
                    'resolved_at' => $resolved
                        ? fake()->dateTimeBetween($pullRequest->created_at, $pullRequest->updated_at ?? now())
                        : null,
                    'resolved_by_user_id' => $resolved ? $commenters->random() : null,
                ];
            };

            $threads = Thread::factory(rand(0, 3), $attrsCallback)->create();

            foreach ($threads as $thread) {
                $attrs = [
                    'created_at' => fake()->dateTimeBetween($pullRequest->created_at, $pullRequest->updated_at ?? now()),
                    'pull_request_id' => $pullRequest->id,
                    'vcs_instance_user_id' => $commenters->random(),
                    'thread_id' => $thread->id,
                ];

                Comment::factory(rand(1, 3), $attrs)->create();
            }
        }
    }
}
