<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

/**
 * @property int $id
 * @property string $title
 * @property string $menu_title
 * @property string $slug
 * @property string $content
 * @property string $description
 * @property int|null $parent_id
 *
 * @property int $depth
 * @property Page $parent
 * @property Page[] $children
 */
class Page extends Model
{
    // подключаем трейт от нестедсет
    use NodeTrait;

    protected $table = 'pages';
    protected $guarded = [];

    /** Получаем полный путь к странице */
    public function getPath(): string
    {
        return implode('/', array_merge($this->ancestors()->defaultOrder()->pluck('slug')->toArray(), [$this->slug]));
    }

    /** Получаем название в меню */
    public function getMenuTitle(): string
    {
        return $this->menu_title ?: $this->title;
    }
}