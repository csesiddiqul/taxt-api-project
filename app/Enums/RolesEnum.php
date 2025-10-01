<?php

namespace App\Enums;

enum RolesEnum: int
{
    case Admin = 1;
    case Administration = 2;
    case Guest = 3;
    case User = 4;
    case Patient = 5;
    case Doctor = 6;

    public static function asList(): array
    {
        return [
            self::Administration->value => 'administration',
            self::Admin->value => 'admin',
            self::Guest->value => 'guest',
            self::Patient->value => 'patient',
            self::Doctor->value => 'doctor',
        ];
    }

    public static function getValueWithRole(int $value): ?string
    {
        return self::asList()[$value] ?? null;
    }
}
