<?php

namespace App\Enums;

use ReflectionClass;

abstract class BaseEnum
{
    public static function getAllConsts(): array
    {
        $reflect = new ReflectionClass(static::class);
        return array_values($reflect->getConstants()); 
    }
}
