<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Approver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Approver>
 */
class ApproverFactory extends Factory
{
    protected $model = Approver::class;

    public function definition(): array
    {
        return [
            'approved_at' => Carbon::now(),
        ];
    }
}
