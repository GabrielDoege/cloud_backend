<?php

namespace App\Enums;

abstract class EnumRentalStatus extends BaseEnum
{
    public const ACTIVE   = 1;
    public const FINISHED = 2;
    public const CANCELED = 3;
}