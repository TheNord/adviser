<?php

namespace App\UseCases\Adverts;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Advert\Dialog\Dialog;
use Illuminate\Support\Facades\Auth;

class DialogService
{
    /** Чтение сообщения */
    public function read($dialogId, Advert $advert)
    {
        $dialog = Dialog::findOrFail($dialogId);

        // читаем сообщение от имени автора
        if (Auth::id() === $dialog->user_id)
        {
            $advert->readClientMessages($dialog->id);
        }

        // читаем сообщение от имени написавшего пользователя
        if (Auth::id() === $dialog->client_id)
        {
            $advert->readOwnerMessages($dialog->id);
        }
    }

    /** Добавление нового сообщения к диалогу */
    public function write($clientId, $message, $dialog)
    {
        // пишем сообщение написавшему нам пользователю
        if ($clientId === $dialog->user_id)
        {
            $dialog->writeMessageByClient($clientId, $message);
        }

        // пишем сообщение автору объявления
        if ($clientId === $dialog->client_id)
        {
            $dialog->writeMessageByOwner($clientId, $message);
        }
    }
}
