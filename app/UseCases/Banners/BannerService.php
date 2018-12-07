<?php

namespace App\UseCases\Banners;

use App\Entity\Adverts\Category;
use App\Entity\Banner\Banner;
use App\Entity\Region;
use App\Entity\User;
use App\Http\Requests\Banner\CreateRequest;
use App\Http\Requests\Banner\EditRequest;
use App\Http\Requests\Banner\FileRequest;
use App\Http\Requests\Banner\RejectRequest;
use App\Services\Banner\CostCalculator;
use App\Services\Search\BannerIndexer;
use Carbon\Carbon;
use Elasticsearch\Client;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    private $calculator;
    private $client;
    private $indexer;

    /** Получение калькулятора и клиента Elasticsearch */
    public function __construct(CostCalculator $calculator, Client $client, BannerIndexer $indexer)
    {
        $this->calculator = $calculator;
        $this->client = $client;
        $this->indexer = $indexer;
    }

    /** Получаем баннер */
    public function getRandomForView(?int $categoryId, ?int $regionId, $format): ?Banner
    {
        $response = $this->client->search([
            'index' => 'banners',
            'type' => 'banner',
            'body' => [
                '_source' => ['id'],
                // берем 5 баннеров, вдруг какие-то устарели
                'size' => 5,
                // сортируем рандомно
                'sort' => [
                    '_script' => [
                        'type' => 'number',
                        'script' => 'Math.random() * 200000',
                        'order' => 'asc',
                    ],
                ],
                // правила поиска
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['status' => Banner::STATUS_ACTIVE]],
                            ['term' => ['format' => $format ?: '']],
                            ['term' => ['categories' => $categoryId ?: 0]],
                            ['term' => ['regions' => $regionId ?: 0]],
                        ],
                    ],
                ],
            ],
        ]);

        dd($response);

        // получаем список идшников
        if (!$ids = array_column($response['hits']['hits'], '_id')) {
            return null;
        }

        // по полученным ид находим баннер (Первый)
        $banner = Banner::active()
            ->with(['category', 'region'])
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id,' . implode(',', $ids) . ')')
            ->first();

        if (!$banner) {
            return null;
        }

        // если находим баннер, добавляем показ этому баннеру
        $banner->view();

        return $banner;
    }

    /** Создание баннера */
    public function create(User $user, Category $category, ?Region $region, CreateRequest $request): Banner
    {
        /** @var Banner $banner */
        $banner = Banner::make([
            'name' => $request['name'],
            'limit' => $request['limit'],
            'url' => $request['url'],
            'format' => $request['format'],
            // получаем из реквеста файл, сохраняем его /public/uploads/banners с разбивкой по датам
            'file' => $request->file('file')->store('uploads/banners/' . date('Y/m')),
            'status' => Banner::STATUS_DRAFT,
        ]);

        // связываем данные
        // user_id = $user->id, category_id = $category->id, region_id = $region->id
        $banner->user()->associate($user);
        $banner->category()->associate($category);
        $banner->region()->associate($region);

        $banner->saveOrFail();

        return $banner;
    }

    /** Редактирование картинки (баннера) */
    public function changeFile($id, FileRequest $request): void
    {
        $banner = $this->getBanner($id);

        if (!$banner->canBeChanged()) {
            throw new \DomainException('Unable to edit the banner.');
        }
        // удаляем старые картинки
        Storage::delete('public/' . $banner->file);

        $banner->update([
            'format' => $request['format'],
            'file' => $request->file('file')->store('banners', 'public'),
        ]);
    }

    /** Редактирование баннера от пользователя */
    public function editByOwner($id, EditRequest $request): void
    {
        // получаем баннер
        $banner = $this->getBanner($id);
        // проверка на доступность редактирования (только черновик)
        if (!$banner->canBeChanged()) {
            throw new \DomainException('Unable to edit the banner.');
        }
        // изменение баннера
        $banner->update([
            'name' => $request['name'],
            'limit' => $request['limit'],
            'url' => $request['url'],
        ]);
    }

    /** Редактирование баннера от администратора */
    public function editByAdmin($id, EditRequest $request): void
    {
        $banner = $this->getBanner($id);
        $banner->update([
            'name' => $request['name'],
            'limit' => $request['limit'],
            'url' => $request['url'],
        ]);
    }

    /** Отправка баннера на модерацию */
    public function sendToModeration($id): void
    {
        $banner = $this->getBanner($id);
        $banner->sendToModeration();
    }

    /** Отменить модерацию баннера */
    public function cancelModeration($id): void
    {
        $banner = $this->getBanner($id);
        $banner->cancelModeration();
    }

    /** Модерирование баннера (одобрение) */
    public function moderate($id): void
    {
        $banner = $this->getBanner($id);
        $banner->moderate();
    }

    /** Отклонение баннера с причиной */
    public function reject($id, RejectRequest $request): void
    {
        $banner = $this->getBanner($id);
        $banner->reject($request['reason']);
    }

    /** Формирование ордера */
    public function order($id): Banner
    {
        // получаем баннер
        $banner = $this->getBanner($id);
        // просчет стоимости за 1000 показов
        $cost = $this->calculator->calc($banner->limit);
        // меняем статус и записываем стоимость размещения
        $banner->order($cost);

        return $banner;
    }

    /** Изменить статус на оплаченный */
    public function pay($id): void
    {
        $banner = $this->getBanner($id);
        // передаем текущую дату как дату публикации
        $banner->pay(Carbon::now());

        $this->indexer->index($banner);
    }

    /** Клик по баннеру */
    public function click(Banner $banner): void
    {
        $banner->click();
    }

    /** Получение баннера по id */
    private function getBanner($id): Banner
    {
        return Banner::findOrFail($id);
    }

    /** Удаление от пользователя */
    public function removeByOwner($id): void
    {
        $banner = $this->getBanner($id);

        // удаление доступно только когда баннер в черновиках
        if (!$banner->canBeRemoved()) {
            throw new \DomainException('Unable to remove the banner.');
        }
        $banner->delete();
        Storage::delete('public/' . $banner->file);
    }

    /** Удаление от администратора */
    public function removeByAdmin($id): void
    {
        $banner = $this->getBanner($id);
        $banner->delete();
        Storage::delete('public/' . $banner->file);
    }
}
