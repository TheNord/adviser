<?php

namespace App\Http\Resources\Adverts;

use App\Entity\Adverts\Advert\Photo;
use App\Entity\Adverts\Advert\Value;
use App\Entity\Adverts\Attribute;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Вывод подробной информации об объявлении
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $region_id
 * @property string $title
 * @property string $content
 * @property int $price
 * @property string $address
 * @property Carbon $published_at
 * @property Carbon $expires_at
 *
 * @property User $user
 * @property Region $region
 * @property Category $category
 * @property Value[] $values
 * @property Photo[]|Collection $photos
 *
 * @method  mixed getValue($id)
 */
class AdvertDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'region' => $this->region ? [
                'id' => $this->region->id,
                'name' => $this->region->name,
            ] : [],
            'title' => $this->title,
            'content' => $this->content,
            'price' => $this->price,
            'address' => $this->address,
            'date' => [
                'published' => $this->published_at,
                'expires' => $this->expires_at,
            ],
            // выводим все атрибуты
            'values' => array_map(function (Attribute $attribute) {
                return [
                    'name' => $attribute->name,
                    'value' => $this->getValue($attribute->id),
                ];
            }, $this->category->allAttributes()),
            // выводим все фото
            'photos' => $this->photos()->get()->all(),
        ];
    }
}

/**
 * @SWG\Definition(
 *     definition="AdvertDetail",
 *     type="object",
 *     @SWG\Property(property="id", type="integer"),
 *     @SWG\Property(property="user", type="object",
 *         @SWG\Property(property="name", type="string"),
 *         @SWG\Property(property="phone", type="string"),
 *     ),
 *     @SWG\Property(property="category", type="object",
 *         @SWG\Property(property="id", type="integer"),
 *         @SWG\Property(property="name", type="string"),
 *     ),
 *     @SWG\Property(property="region", type="object",
 *         @SWG\Property(property="id", type="integer"),
 *         @SWG\Property(property="name", type="string"),
 *     ),
 *     @SWG\Property(property="title", type="string"),
 *     @SWG\Property(property="content", type="string"),
 *     @SWG\Property(property="price", type="integer"),
 *     @SWG\Property(property="address", type="string"),
 *     @SWG\Property(property="date", type="object",
 *         @SWG\Property(property="published", type="date"),
 *         @SWG\Property(property="expires", type="date"),
 *     ),
 *     @SWG\Property(property="values", type="array", @SWG\Items(ref="#/definitions/AdvertValue")),
 *     @SWG\Property(property="photos", type="array", @SWG\Items(type="string")),
 * )
 *
 * @SWG\Definition(
 *     definition="AdvertValue",
 *     type="object",
 *     @SWG\Property(property="name", type="string"),
 *     @SWG\Property(property="value", type="string"),
 * )
 */