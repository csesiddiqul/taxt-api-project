<?php

namespace App\Enums;

enum StatusEnum: int
{
    case Active = 1;
    case Inactive = 0;


    public static function asList(): array
    {
        return [
            self::Active->value => self::Active,
            self::Inactive->value => self::Inactive
        ];
    }


}
