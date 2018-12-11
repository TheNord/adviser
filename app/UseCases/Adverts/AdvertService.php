<?php

namespace App\UseCases\Adverts;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Entity\User;
use App\Events\Advert\ModerationPassed;
use App\Http\Requests\Adverts\AttributesRequest;
use App\Http\Requests\Adverts\CreateRequest;
use App\Http\Requests\Adverts\EditRequest;
use App\Http\Requests\Adverts\PhotosRequest;
use App\Http\Requests\Adverts\RejectRequest;
use App\Notifications\Advert\ModerationPassedNotification;
use App\Services\Search\AdvertIndexer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdvertService
{
    private $indexer;

    public function __construct(AdvertIndexer $indexer)
    {
        $this->indexer = $indexer;
    }

    /** Создание нового объявления */
    public function create($userId, $categoryId, $regionId, CreateRequest $request): Advert
    {
        /** @var User $user */
        $user = User::findOrFail($userId);
        /** @var Category $category */
        $category = Category::findOrFail($categoryId);
        /** @var Region $region */
        $region = $regionId ? Region::findOrFail($regionId) : null;

        return DB::transaction(function () use ($request, $user, $category, $region) {

            /** @var Advert $advert */
            $advert = Advert::make([
                'title' => $request['title'],
                'content' => $request['content'],
                'price' => $request['price'],
                'address' => $request['address'],
                'status' => Advert::STATUS_DRAFT,
            ]);

            // производим ассоциативное связывание
            $advert->user()->associate($user);
            $advert->category()->associate($category);
            $advert->region()->associate($region);

            $advert->saveOrFail();

            // Проходим по всем аттрибутам категории
            foreach ($category->allAttributes() as $attribute) {
                // из реквеста получаем ид аттрибута
                $value = $request['attributes'][$attribute->id] ?? null;
                // если оно не пустое то добавляем через связь
                if (!empty($value)) {
                    $advert->values()->create([
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ]);
                }
            }
            return $advert;
        });
    }

    /** Добавление фотографий к объявлению */
    public function addPhotos($id, PhotosRequest $request): void
    {
        $advert = $this->getAdvert($id);

        DB::transaction(function () use ($request, $advert) {
            foreach ($request['files'] as $file) {
                $advert->photos()->create([
                    'file' => $file->store('/uploads/adverts')
                ]);
            }
            $advert->update();
        });
    }

    /** Редактирование объявления */
    public function edit($id, EditRequest $request): void
    {
        $advert = $this->getAdvert($id);
        $advert->update($request->only([
            'title',
            'content',
            'price',
            'address',
        ]));
    }

    /** Отправка на модерацию */
    public function sendToModeration($id): void
    {
        $advert = $this->getAdvert($id);
        $advert->sendToModeration();
    }

    /** Одобрение модерации */
    public function moderate($id): void
    {
        $advert = $this->getAdvert($id);
        $advert->moderate(Carbon::now());

        // индексируем и уведомляем пользователя об успешном прохождении модерации объявления
        event(new ModerationPassed($advert));
    }

    /** Отклонение модерации */
    public function reject($id, RejectRequest $request): void
    {
        $advert = $this->getAdvert($id);
        $advert->reject($request['reason']);

        $this->indexer->remove($advert);
    }

    /** Редактирование аттрибутов */
    public function editAttributes($id, AttributesRequest $request): void
    {
        $advert = $this->getAdvert($id);

        DB::transaction(function () use ($request, $advert) {
            // сперва удаляем через связь атрибуты
            $advert->values()->delete();
            // проходим по всем атрибутам категории
            foreach ($advert->category->allAttributes() as $attribute) {
                $value = $request['attributes'][$attribute->id] ?? null;
                // если не  пустое то заносим в бд
                if (!empty($value)) {
                    $advert->values()->create([
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ]);
                }
            }
            $advert->update();
        });
    }

    /** Истечение срока размещения объявления (статус на Close) */
    public function expire(Advert $advert): void
    {
        $advert->expire();

        $this->indexer->remove($advert);
    }

    /** Закрытие объявления */
    public function close($id): void
    {
        $advert = $this->getAdvert($id);
        $advert->close();

        $this->indexer->remove($advert);
    }

    /** Удаление объявления */
    public function remove($id): void
    {
        $advert = $this->getAdvert($id);
        $advert->delete();

        $this->indexer->remove($advert);
    }

    /** Получение объявления из бд */
    private function getAdvert($id): Advert
    {
        return Advert::findOrFail($id);
    }
}
