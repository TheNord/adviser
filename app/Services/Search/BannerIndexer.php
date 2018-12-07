<?php

namespace App\Services\Search;

use App\Entity\Banner\Banner;
use App\Entity\Region;
use Elasticsearch\Client;

class BannerIndexer
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /** Очищаем все баннеры */
    public function clear(): void
    {
        $this->client->deleteByQuery([
            'index' => 'banners',
            'type' => 'banner',
            'body' => [
                'query' => [
                    'match_all' => new \stdClass(),
                ],
            ],
        ]);
    }

    /** Добавляем баннер в индекс */
    public function index(Banner $banner): void
    {
        $regionIds = [0];

        // добавляем баннер во все подрегионы (Москва >> Балашиха, Химки и тд)
        if ($banner->region) {
            $regionIds = [$banner->region->id];
            $childrenIds = $regionIds;
            while ($childrenIds = Region::whereIn('parent_id', $childrenIds)->pluck('id')->toArray()) {
                $regionIds = array_merge($regionIds, $childrenIds);
            }
        }

        $this->client->index([
            'index' => 'banners',
            'type' => 'banner',
            'id' => $banner->id,
            'body' => [
                'id' => $banner->id,
                'status' => $banner->status,
                'format' => $banner->format,
                'categories' => array_merge(
                    [$banner->category->id],
                    // добавляем все подкатегории
                    $banner->category->descendants()->pluck('id')->toArray()
                ),
                'regions' => $regionIds,
            ],
        ]);
    }

    public function remove(Banner $banner): void
    {
        $this->client->delete([
            'index' => 'banners',
            'type' => 'banner',
            'id' => $banner->id,
        ]);
    }
}