<?php

namespace App\Services\Banner;

class CostCalculator
{
    private $price;

    /** Цену получаем из env через Фабрику в AppServiceProvider */
    public function __construct(int $price)
    {
        $this->price = $price;
    }

    /** Просчитываем стоимость заказа */
    public function calc(int $views): int
    {
        return floor($this->price * ($views / 1000));
    }
}
