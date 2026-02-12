<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use Illuminate\Container\Attributes\Scoped;

#[Scoped]
final class UserActivityCollector
{
    /**
     * @var int[]
     */
    private array $userIds = [];

    /**
     * @param int[] $userIds
     */
    public function addUserIds(array $userIds): void
    {
        $this->userIds = array_merge($this->userIds, $userIds);
    }

    /**
     * @return int[]
     */
    public function getUserIds(): array
    {
        return array_unique($this->userIds);
    }
}
