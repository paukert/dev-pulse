<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\User;
use App\Models\VcsInstance;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        PullRequest::factory(100)
            ->hasMetrics()
            ->create();
    }
}
