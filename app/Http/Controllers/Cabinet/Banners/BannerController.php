<?php

namespace App\Http\Controllers\Cabinet\Banners;

use App\Entity\Banner\Banner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\EditRequest;
use App\Http\Requests\Banner\FileRequest;
use App\Services\Robokassa\Robokassa;
use App\UseCases\Banners\BannerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BannerController extends Controller
{
    private $service;
    private $robokassa;

    /**
     * Принимаем BannerService
     */
    public function __construct(BannerService $service, Robokassa $robokassa)
    {
        $this->service = $service;
        $this->robokassa = $robokassa;
    }

    /**
     * Получаем список всех баннеров для текущего пользователя (scopeForUser)
     * с сортировкой по ID и пагинацией
     */
    public function index()
    {
        $banners = Banner::forUser(Auth::user())->orderByDesc('id')->paginate(20);

        return view('cabinet.banners.index', compact('banners'));
    }

    /**
     * Отображение информации о баннере
     */
    public function show(Banner $banner)
    {
        // проверка на возможность редактирования баннера
        $this->checkAccess($banner);

        return view('cabinet.banners.show', compact('banner'));
    }

    /**
     * Отображаем форму редактирования объявления
     */
    public function editForm(Banner $banner)
    {
        $this->checkAccess($banner);

        // Не выводим форму редактирования если баннер не может быть изменен
        // Согласно бизнес логике проекта, баннер не может меняться если ранее
        // был отмодерирован или отправлен на модерацию
        // И если статус любой другой кроме черновика - отказываем в доступе
        if (!$banner->canBeChanged()) {
            return redirect()->route('cabinet.banners.show', $banner)->with('error', 'Unable to edit.');
        }

        return view('cabinet.banners.edit', compact('banner'));
    }

    /** Редактирование баннера */
    public function edit(EditRequest $request, Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            // редактирование собственного баннера пользователем
            $this->service->editByOwner($banner->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }

    /** Форма загрузки файлов */
    public function fileForm(Banner $banner)
    {
        $this->checkAccess($banner);
        if (!$banner->canBeChanged()) {
            return redirect()->route('cabinet.banners.show', $banner)->with('error', 'Unable to edit.');
        }
        $formats = Banner::formatsList();
        return view('cabinet.banners.file', compact('banner', 'formats'));
    }

    /** Загрузка новых файлов (баннера) */
    public function file(FileRequest $request, Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            $this->service->changeFile($banner->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }

    /** Отправка на модерацию */
    public function send(Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            $this->service->sendToModeration($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }

    /** Отмена отправки на модерацию */
    public function cancel(Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            $this->service->cancelModeration($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }

    /** Формируем ордер к оплате */
    public function order(Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            // формируем стоимость и изменяем статус
            $banner = $this->service->order($banner->id);
            // генерируем ссылку для оплаты
            $url = $this->robokassa->generateRedirectUrl($banner->id, $banner->cost, 'banner');
            return redirect($url);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }

    /** Удаление объявления */
    public function destroy(Banner $banner)
    {
        $this->checkAccess($banner);
        try {
            $this->service->removeByOwner($banner->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.index');
    }

    /**
     * @param Banner $banner
     *
     * Проверка на доступность редактирования баннера
     *  (banner->user_id = user->id)
     */
    private function checkAccess(Banner $banner): void
    {
        if (!Gate::allows('manage-own-banner', $banner)) {
            abort(403);
        }
    }
}
