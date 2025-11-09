<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\VcsInstance;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        VcsInstance::factory(4)->create();

        User::factory(50)->create();
    }
}
