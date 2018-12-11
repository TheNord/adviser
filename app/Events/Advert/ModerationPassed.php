<?php

namespace App\Events\Advert;

use App\Entity\Adverts\Advert\Advert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

/** Событие о прохождении модерации
 *  В EventServiceProvider навешена обработка
 *  ModerationPassedListener и AdvertChangedListener
 */
class ModerationPassed
{
    public $advert;

    public function __construct(Advert $advert)
    {
        $this->advert = $advert;
    }
}