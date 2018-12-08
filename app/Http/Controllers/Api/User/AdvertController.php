<?php

namespace App\Http\Controllers\Api\User;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Adverts\AttributesRequest;
use App\Http\Requests\Adverts\CreateRequest;
use App\Http\Requests\Adverts\EditRequest;
use App\Http\Requests\Adverts\PhotosRequest;
use App\Http\Resources\Adverts\AdvertDetailResource;
use App\Http\Resources\Adverts\AdvertListResource;
use App\UseCases\Adverts\AdvertService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Контроллер объявлений в кабинете пользователя */
class AdvertController extends Controller
{
    private $service;

    public function __construct(AdvertService $service)
    {
        $this->service = $service;
    }

    /** Выводим список собственнх объявлений */
    public function index()
    {
        $adverts = Advert::forUser(Auth::user())->orderByDesc('id')->paginate(20);
        // оборачиваем в AdvertListResource и паджинируем результат
        return AdvertListResource::collection($adverts);
    }

    /** Просматриваем детали объявления */
    public function show(Advert $advert)
    {
        $this->checkAccess($advert);
        return new AdvertDetailResource($advert);
    }

    /** Добавление нового объявления */
    public function store(CreateRequest $request, Category $category, Region $region = null)
    {
        $advert = $this->service->create(
            Auth::id(),
            $category->id,
            $region ? $region->id : null,
            $request
        );

        return (new AdvertDetailResource($advert))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /** Изменение объявления */
    public function update(EditRequest $request, Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->edit($advert->id, $request);
        return new AdvertDetailResource(Advert::findOrFail($advert->id));
    }

    public function attributes(AttributesRequest $request, Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->editAttributes($advert->id, $request);
        // возвращаем измененное объявление
        return new AdvertDetailResource(Advert::findOrFail($advert->id));
    }

    /** Добавление фотографий к объявлению */
    public function photos(PhotosRequest $request, Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->addPhotos($advert->id, $request);
        return new AdvertDetailResource(Advert::findOrFail($advert->id));
    }

    /** Отправка на модерацию */
    public function send(Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->sendToModeration($advert->id);
        return new AdvertDetailResource(Advert::findOrFail($advert->id));
    }

    /** Отмена отправки на модерацию */
    public function close(Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->close($advert->id);
        return new AdvertDetailResource(Advert::findOrFail($advert->id));
    }

    /** Удаление объявления */
    public function destroy(Advert $advert)
    {
        $this->checkAccess($advert);
        $this->service->remove($advert->id);
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /** Проверка доступа к собственному объявлению */
    private function checkAccess(Advert $advert): void
    {
        if (!Gate::allows('manage-own-advert', $advert)) {
            abort(403);
        }
    }
}