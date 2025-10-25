<?php

namespace App\Bo;

class RentalPriceBo
{

    CONST MARGEM_LUCRO = 4;

    public static function calculate(int $durationSeconds): float
    {
        $minutes        = self::getQuantidadeMinutos($durationSeconds);
        $valorPorMinuto = self::getCustoMinuto() * self::MARGEM_LUCRO;

        $final_price = $minutes * $valorPorMinuto * (1 - self::getDescontoMinuto($minutes));

        return round($final_price, 2);
    }

    private static function getQuantidadeMinutos(int $durationSeconds): int
    {
        return max(1, (int) ceil($durationSeconds / 60));
    }

    private static function getCustoMinuto(): float
    {
        return self::getCustoAguaMinuto() + self::getCustoEnergiaMinuto();
    }

    private static function getDescontoMinuto(int $minutes): float
    {
        if ($minutes <= 10) {
            return 0.0;

        } elseif ($minutes <= 30) {
            return 0.05;

        } elseif ($minutes <= 60) {
            return 0.10;

        } elseif ($minutes <= 120) {
            return 0.15;
        } 

        return 0.20;
    }

    private static function getCustoAguaMinuto(): float
    {
        $litrosPorMinuto = 10.0;
        $custoAguaPorMinuto = 0.01;

        return $litrosPorMinuto * $custoAguaPorMinuto;
    }

    private static function getCustoEnergiaMinuto(): float
    {
        $custoEnergiaPorMinuto = 0.02;
        return $custoEnergiaPorMinuto;
    }
}