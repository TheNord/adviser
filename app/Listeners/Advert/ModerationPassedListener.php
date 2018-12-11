<?php

namespace App\Listeners\Advert;

use App\Events\Advert\ModerationPassed;
use App\Notifications\Advert\ModerationPassedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/** Слушатель для события ModerationPassed (Events) */
class ModerationPassedListener implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function handle(ModerationPassed $event): void
    {
        // получаем из ModerationPassed объявление
        $advert = $event->advert;
        // уведомляем пользователя о прохождении модерации
        $advert->user->notify(new ModerationPassedNotification($advert));
    }
}