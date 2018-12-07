<?php

namespace App\Entity\Banner;

use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Entity\User;
use App\Services\Search\BannerIndexer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $region_id
 * @property string $name
 * @property int $views
 * @property int $limit
 * @property int $clicks
 * @property int $cost
 * @property string $url
 * @property string $format
 * @property string $file
 * @property string $status
 * @property Carbon $publishCarbon
 *
 * @property Region|null $region
 * @property Category $category
 *
 * @method Builder active()
 * @method Builder forUser(User $user)
 */
class Banner extends Model
{
    // статусы баннера
    public const STATUS_DRAFT = 'draft';
    public const STATUS_MODERATION = 'moderation';
    public const STATUS_MODERATED = 'moderated';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'close';

    // таблица
    protected $table = 'banner_banners';

    // какие поля запрещены к массовому присвоению
    protected $guarded = ['id'];

    // поле published_at преобразовывать в Carbon
    protected $casts = [
        'published_at' => 'datetime',
    ];

    private $indexer;


    // лист статусов
    public static function statusesList(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_MODERATION => 'On Moderation',
            self::STATUS_MODERATED => 'moderated',
            self::STATUS_ORDERED => 'Payment',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    // список форматов
    public static function formatsList(): array
    {
        return [
            '240x400',
        ];
    }
    /** Добавляем число показов к баннеру */
    public function view(): void
    {
        // проверка на активность баннера
        $this->assertIsActive();
        // добавляем число показов
        $this->views++;
        // проверка на лимит показов, если превышен то закрываем баннер
        if ($this->views >= $this->limit) {
            $this->status = self::STATUS_CLOSED;
        }
        $this->save();
    }

    /** Добавляем число кликов */
    public function click(): void
    {
        $this->assertIsActive();
        $this->clicks++;
        $this->save();
    }

    /** Отправка на модерацию */
    public function sendToModeration(): void
    {
        if (!$this->isDraft()) {
            throw new \DomainException('Advert is not draft.');
        }
        $this->update([
            'status' => self::STATUS_MODERATION,
        ]);
    }

    /** Отмена модерации */
    public function cancelModeration(): void
    {
        if (!$this->isOnModeration()) {
            throw new \DomainException('Advert is not sent to moderation.');
        }
        $this->update([
            'status' => self::STATUS_DRAFT,
        ]);
    }

    /** Промодерировать баннер */
    public function moderate(): void
    {
        if (!$this->isOnModeration()) {
            throw new \DomainException('Advert is not sent to moderation.');
        }
        $this->update([
            'status' => self::STATUS_MODERATED,
        ]);
    }

    /** Отклонение баннера */
    public function reject($reason): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'reject_reason' => $reason,
        ]);
    }

    /** Перевод баннера в статус ordered с запоминанием стоимости */
    public function order(int $cost): void
    {
        if (!$this->isModerated()) {
            throw new \DomainException('Advert is not moderated.');
        }
        $this->update([
            'cost' => $cost,
            'status' => self::STATUS_ORDERED,
        ]);
    }

    /** Перевод баннера в оплаченный статус */
    public function pay(Carbon $date): void
    {
        if (!$this->isOrdered()) {
            throw new \DomainException('Advert is not ordered.');
        }
        $this->update([
            'published_at' => $date,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /** Получение ширины */
    public function getWidth(): int
    {
        return explode('x', $this->format)[0];
    }

    /** Получение высоты */
    public function getHeight(): int
    {
        return explode('x', $this->format)[1];
    }

    public function canBeChanged(): bool
    {
        return $this->isDraft();
    }

    public function canBeRemoved(): bool
    {
        return $this->isDraft();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isOnModeration(): bool
    {
        return $this->status === self::STATUS_MODERATION;
    }

    public function isModerated(): bool
    {
        return $this->status === self::STATUS_MODERATED;
    }

    public function isOrdered(): bool
    {
        return $this->status === self::STATUS_ORDERED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** Связывающие таблицы */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /** Скопы активности и пользователя */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /** Проверка активности баннера */
    private function assertIsActive(): void
    {
        if (!$this->isActive()) {
            throw new \DomainException('Banner is not active.');
        }
    }
}
