<?php

namespace App\Services\Sms;

use GuzzleHttp\Client;

class SmsRu implements SmsSender
{
    private $appId;
    private $url;
    private $client;

    public function __construct($appId)
    {
        if (empty($appId)){
            throw new \InvalidArgumentException('Sms appId must be set.');
        }

        $this->appId = $appId;
        $this->url = "https://sms.ru/sms/send";
        $this->client = new Client();
    }

    public function send($number, $text): void
    {
        $this->client->post($this->url, [
            'form_params' => [
             'api_id' => $this->appId,
             'to' => '+' . $number,
             'text' => $text
            ]
        ]);
    }
}