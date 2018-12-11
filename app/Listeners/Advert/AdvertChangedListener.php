<?php

namespace App\Listeners\Advert;

use App\Services\Search\AdvertIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

/** Слушатель для события изменения объявления */
class AdvertChangedListener implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $indexer;

    public function __construct(AdvertIndexer $indexer)
    {
        $this->indexer = $indexer;
    }

    public function handle($event): void
    {
        $this->indexer->index($event->advert);
    }
}