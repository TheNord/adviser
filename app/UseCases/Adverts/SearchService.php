<?php

namespace App\UseCases\Adverts;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Http\Requests\Adverts\SearchRequest;
use Elasticsearch\Client;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function search(?Category $category, ?Region $region, SearchRequest $request, int $perPage, int $page): Paginator
    {
        // получить только заполенные атрибуты
        $values = array_filter((array)$request->input('attrs'), function ($value) {
            return !empty($value['equals']) || !empty($value['from']) || !empty($value['to']);
        });

        $response = $this->client->search([
            'index' => 'app',
            'type' => 'advert',
            'body' => [
                '_source' => ['id'],
                // пагинация
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                // сортируем по релевантности если это поисковой запрос, и по дате публикации если это вывод на сайте
                'sort' => empty($request['text']) ? [
                    ['published_at' => ['order' => 'desc']],
                ] : [],
                'query' => [
                    'bool' => [
                        'must' => array_merge(
                            [
                                // дополнительная проверка на статус активности
                                ['term' => ['status' => Advert::STATUS_ACTIVE]],
                            ],
                            array_filter([
                                // если категория указанна то фильтруем по категории
                                $category ? ['term' => ['categories' => $category->id]] : false,
                                $region ? ['term' => ['regions' => $region->id]] : false,
                                // если текст не пуст то делаем поле тайтл более важным при поиске
                                !empty($request['text']) ? ['multi_match' => [
                                    'query' => $request['text'],
                                    'fields' => ['title^3', 'content']
                                ]] : false,
                            ]),
                            array_map(function ($value, $id) {
                                return [
                                    'nested' => [
                                        'path' => 'values',
                                        'query' => [
                                            'bool' => [
                                                'must' => array_values(array_filter([
                                                    ['match' => ['values.attribute' => $id]],
                                                    !empty($value['equals']) ? ['match' => ['values.value_string' => $value['equals']]] : false,
                                                    !empty($value['from']) ? ['range' => ['values.value_int' => ['gte' => $value['from']]]] : false,
                                                    !empty($value['to']) ? ['range' => ['values.value_int' => ['lte' => $value['to']]]] : false,
                                                ])),
                                            ],
                                        ],
                                    ],
                                ];
                            }, $values, array_keys($values))
                        )
                    ],
                ],
            ],
        ]);

        $ids = array_column($response['hits']['hits'], '_id');

        if (!$ids) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        $items = Advert::active()
            ->with(['category', 'region'])
            ->whereIn('id', $ids)
            ->orderBy(new Expression('FIELD(id,' . implode(',', $ids) . ')'))
            ->get();

        return new LengthAwarePaginator($items, $response['hits']['total'], $perPage, $page);
    }
}