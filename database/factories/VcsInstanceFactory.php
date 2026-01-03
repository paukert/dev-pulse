<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VcsPlatform;
use App\Models\VcsInstance;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends Factory<VcsInstance>
 */
class VcsInstanceFactory extends Factory
{
    protected $model = VcsInstance::class;

    public function definition(): array
    {
        return match (fake()->randomElement(VcsPlatform::cases())) {
            VcsPlatform::GITHUB => [
                'name' => 'GitHub - ' . fake()->domainWord(),
                'api_url' => 'https://api.github.com/',
                'installation_id' => fake()->randomNumber(4),
                'platform' => VcsPlatform::GITHUB,
            ],
            VcsPlatform::GITLAB => [
                'name' => 'GitLab - ' . fake()->domainWord(),
                'api_url' => 'https://gitlab.com/api/',
                'token' => fake()->uuid(),
                'platform' => VcsPlatform::GITLAB,
            ],
            default => throw new LogicException('Unsupported platform'),
        };
    }
}
