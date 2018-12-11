<?php

namespace App\Notifications\Advert;

use App\Entity\Adverts\Advert\Advert;
use App\Notifications\SmsChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/** Уведомления о прохождении модерации (с очередью) */
class ModerationPassedNotification extends Notification
{
    private $advert;

    public function __construct(Advert $advert)
    {
        $this->advert = $advert;
    }

    /** отправляем через почту и смс */
    public function via($notifiable)
    {
        return ['mail', SmsChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Moderation is passed')
            ->greeting('Hello!')
            ->line('Your advert successfully passed a moderation.')
            ->action('View Advert', route('adverts.show', $this->advert))
            ->line('Thank you for using our application!');
    }

    public function toSms(): string
    {
        return 'Your advert "' . $this->advert->title . '" successfully passed a moderation.';
    }
}
