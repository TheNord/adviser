<?php

namespace App\Console\Commands\Banner;

use App\Entity\Banner\Banner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Predis\Client;

/** Действия при истечение баннеров */
class ExpireCommand extends Command
{
    protected $signature = 'banner:expire';

    private $client;

    /** Подключаем клиент Редис */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle(): bool
    {
        $success = true;

        // как только на баннере лимит показов останется меньше 100
        foreach (Banner::active()->whereRaw('`limit` - views < 100')->with('user')->cursor() as $banner) {
            // ключ для редиса
            $key = 'banner_notify_' . $banner->id;
            // если под таким ключем уже имеется запись - переходим к следующему баннеру
            // (письмо ранее было отправленно)
            if ($this->client->get($key)) {
                continue;
            }
            // добавляем отправку писем в очередь
            Mail::to($banner->user->email)->queue(new BannerExpiresSoonMail($banner));
            // добавляем данные в редис, для запрета повторной отправки
            $this->client->set($key, true);
        }

        return $success;
    }
}
