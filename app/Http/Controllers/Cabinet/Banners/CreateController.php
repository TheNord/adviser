<?php

namespace App\Http\Controllers\Cabinet\Banners;

use App\Entity\Adverts\Category;
use App\Entity\Banner\Banner;
use App\Entity\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\CreateRequest;
use App\UseCases\Banners\BannerService;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер по созданию объявлений в личном кабинете пользователя
 */
class CreateController extends Controller
{
    private $service;

    /**
     * Получаем сервис баннеров для дальнейшей работы с ним
     */
    public function __construct(BannerService $service)
    {
        $this->service = $service;
    }

    /**
     *  Выбираем категорию при создании баннера
     */
    public function category()
    {
        $categories = Category::defaultOrder()->withDepth()->get()->toTree();

        return view('cabinet.banners.create.category', compact('categories'));
    }

    /**
     * Выбираем регион при создании баннера
     */
    public function region(Category $category, Region $region = null)
    {
        $regions = Region::where('parent_id', $region ? $region->id : null)->orderBy('name')->get();

        return view('cabinet.banners.create.region', compact('category', 'region', 'regions'));
    }

    /**
     * Достаем из баннера все поддерживаемые форматы и отображаем страницу создания
     */
    public function banner(Category $category, Region $region = null)
    {
        $formats = Banner::formatsList();

        return view('cabinet.banners.create.banner', compact('category', 'region', 'formats'));
    }

    /**
     * После отправки POST запроса с формы сюда приходит request, выбранные категория и регион,
     * вызываем из сервиса метод создания (create) передавая полученные данные
     */
    public function store(CreateRequest $request, Category $category, Region $region = null)
    {
        try {
            $banner = $this->service->create(
                Auth::user(),
                $category,
                $region,
                $request
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.banners.show', $banner);
    }
}
