<?php

namespace App\Notifications;

use App\Entity\User;
use App\Services\Sms\SmsSender;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    private $sender;

    public function __construct(SmsSender $sender)
    {
        $this->sender = $sender;
    }

    /** реализация функции отправки */
    public function send(User $notifiable, Notification $notification): void
    {
        // не отправляем на неверефицированный телефон
        if (!$notifiable->isPhoneVerified()) {
            return;
        }

        // через наше уведомление вызываем метод toSms для получения сообщения
        $message = $notification->toSms($notifiable);
        // отправляем смс сообщение
        $this->sender->send($notifiable->phone, $message);
    }
}