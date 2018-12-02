<?php


namespace App\Services\Sms;


class ArraySender implements SmsSender
{
    private $message = [];

    public function send($number, $text): void
    {
        $this->message[] = [
            'to' => '+' . $number,
            'text' => $text
        ];
    }

    public function getMessages(): array
    {
        return $this->message;
    }
}