<?php

namespace App\Http\Controllers;

use App\Entity\Banner\Banner;
use App\UseCases\Banners\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    private $service;

    public function __construct(BannerService $service)
    {
        $this->service = $service;
    }

    /** Метод для получения баннера по ajax */
    public function get(Request $request)
    {

        // получаем нужный формат, категорию и регион
        $format = $request['format'];
        $category = $request['category'];
        $region = $request['region'];

        // получаем случайный баннер из ES для текущего региона и категории
        if (!$banner = $this->service->getRandomForView($category, $region, $format)) {
            return '';
        }

        return view('banner.get', compact('banner'));
    }

    /** Клик по баннеру c роутинга */
    public function click(Banner $banner)
    {
        $this->service->click($banner);
        return redirect($banner->url);
    }
}
