<?php

namespace App\Bo;

class RentalPriceBo
{
    // Tarifa fixa por minuto
    const PRICE_PER_MINUTE = 1.00;

    public static function calculate(int $durationMinutes): float
    {
        return $durationMinutes * self::PRICE_PER_MINUTE;
    }
}
