<?php

namespace App\Http\Controllers\Api\Adverts;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Http\Controllers\Controller;
use App\Http\Requests\Adverts\SearchRequest;
use App\Http\Resources\Adverts\AdvertDetailResource;
use App\Http\Resources\Adverts\AdvertListResource;
use App\ReadModel\AdvertReadRepository;
use App\UseCases\Adverts\SearchService;
use Illuminate\Support\Facades\Gate;
use Request;

class AdvertController extends Controller
{
    private $search;

    public function __construct(SearchService $search)
    {
        $this->search = $search;
    }

    /** Список объявлений
     *
     * @OA\Get(
     *     path="/adverts",
     *     tags={"Adverts"},
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/definitions/AdvertList")
     *         ),
     *     ),
     *     security={{"Bearer": {}, "OAuth2": {}}}
     * )
     */
    public function index(SearchRequest $request)
    {
        // извлекаем категорию и регион из get параметров (/adverts/?region=1&category=2)
        $region = $request->get('region') ? Region::findOrFail($request->get('region')) : null;
        $category = $request->get('category') ? Category::findOrFail($request->get('category')) : null;

        // ищем в ES подходящие объявления
        $result = $this->search->search($category, $region, $request, 20, $request->get('page', 1));

        // оборачиваем в ресурс для вывода необходимых данных, оборачиваем в коллекцию для пагинации
        return AdvertListResource::collection($result->adverts);
    }

    /** Вывод одного объявления
     *
     * @OA\Get(
     *     path="/adverts/{advertId}",
     *     tags={"Adverts"},
     *     @OA\Parameter(
     *         name="advertId",
     *         description="ID of advert",
     *         in="path",
     *         required=true,
     *         type="integer"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\Schema(ref="#/definitions/AdvertDetail"),
     *     ),
     *     security={{"Bearer": {}, "OAuth2": {}}}
     * )
     */
    public function show(Advert $advert, Request $request)
    {
        if (!($advert->isActive() || Gate::allows('show-advert', $advert))) {
            abort(403);
        }

        return new AdvertDetailResource($advert);
    }
}