<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => __('Admin'),
            self::USER => __('User'),
        };
    }

    public static function getLabels(): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            $roles[$role->value] = $role->getLabel();
        }
        return $roles;
    }
}
