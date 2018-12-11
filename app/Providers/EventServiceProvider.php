<?php

namespace App\Providers;

use App\Events\Advert\ModerationPassed;
use App\Listeners\Advert\AdvertChangedListener;
use App\Listeners\Advert\ModerationPassedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Навышиваем слушателей на событие ModerationPassed
        // Отправляем письмо и смс, индексируем объявление
        ModerationPassed::class => [
            ModerationPassedListener::class,
            AdvertChangedListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        //
    }
}
