<?php

namespace App\Entity\Adverts\Advert;

use App\Entity\Adverts\Advert\Dialog\Dialog;
use App\Entity\Adverts\Category;
use App\Entity\Region;
use App\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $region_id
 * @property string $title
 * @property string $content
 * @property int $price
 * @property string $address
 * @property string $status
 * @property string $reject_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $published_at
 * @property Carbon $expires_at
 *
 * @property User $user
 * @property Region $region
 * @property Category $category
 * @property Value[] $values
 * @property Photo[] $photos
 * @method Builder active()
 * @method Builder forUser(User $user)
 */
class Advert extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_MODERATION = 'moderation';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $table = 'advert_adverts';

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /** Список статусов */
    public static function statusesList(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_MODERATION => 'On Moderation',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /** Отправка объявления на модерацию */
    public function sendToModeration(): void
    {
        if (!$this->isDraft()) {
            throw new \DomainException('Advert is not draft.');
        }
        if (!\count($this->photos)) {
            throw new \DomainException('Upload photos.');
        }
        $this->update([
            'status' => self::STATUS_MODERATION,
        ]);
    }

    /** Модерирование объявления */
    public function moderate(Carbon $date): void
    {
        if ($this->status !== self::STATUS_MODERATION) {
            throw new \DomainException('Advert is not sent to moderation.');
        }
        $this->update([
            'published_at' => $date,
            'expires_at' => $date->copy()->addDays(15),
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /** Отклонение объявления */
    public function reject($reason): void
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'reject_reason' => $reason,
        ]);
    }

    /** Истечение объявления */
    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
        ]);
    }

    /** Закрытие объявления */
    public function close(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
        ]);
    }

    /** Написать новое сообщение автору объявления от клиента */
    public function writeClientMessage(int $fromId, string $message): void
    {
        $this->getOrCreateDialogWith($fromId)->writeMessageByClient($fromId, $message);
    }

    /** Написать ответное сообщение от имени автора объявления */
    public function writeOwnerMessage(int $toId, string $message): void
    {
        $this->getDialogWith($toId)->writeMessageByOwner($this->user_id, $message);
    }

    /** Чтение сообщения от клиента */
    public function readClientMessages(int $dialogId): void
    {
        $this->getDialogWith($dialogId)->readByClient();
    }

    /** Чтение сообщения от владельца */
    public function readOwnerMessages(int $dialogId): void
    {
        $this->getDialogWith($dialogId)->readByOwner();
    }

    /** Поиск диалога */
    public function getDialogWith(int $dialogId): Dialog
    {
        // получаем диалог у которого user_id = id автора объявления
        // и client_id = ид пользователя которому мы хотим ответить
        $dialog = Dialog::where([
            'id' => $dialogId
        ])->first();

        if (!$dialog) {
            throw new \DomainException('Dialog is not found.');
        }

        return $dialog;
    }

    /** Получение или создание нового диалога от клиента */
    public function getOrCreateDialogWith(int $userId): Dialog
    {
        if ($userId === $this->user_id) {
            throw new \DomainException('Cannot send message to myself.');
        }
        // находим либо создаем
        return $this->dialogs()->firstOrCreate([
            'user_id' => $this->user_id,
            'client_id' => $userId,
        ]);
    }


    /** Получить значение атрибута */
    public function getValue($id)
    {
        foreach ($this->values as $value) {
            if ($value->attribute_id === $id) {
                return $value->value;
            }
        }
        return null;
    }

    /** Получаем первую фотографию объявления */
    public function getFirstPhoto()
    {
        return $this->photos()->pluck('file')->first();
    }

    /** Получаем все фотографии объявления */
    public function getPhotos()
    {
        return $this->photos()->pluck('file');
    }

    /** Изменение статуса объявления */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isOnModeration(): bool
    {
        return $this->status === self::STATUS_MODERATION;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** Связь с пользователем */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /** Связь с категорией */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /** Связь с регионом */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /** Связь с атрибутами */
    public function values()
    {
        return $this->hasMany(Value::class, 'advert_id', 'id');
    }

    /** Связь с фотографиями */
    public function photos()
    {
        return $this->hasMany(Photo::class, 'advert_id', 'id');
    }

    /** Связь с избранным */
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'advert_favorites', 'advert_id', 'user_id');
    }

    /** Связь с диалогами */
    public function dialogs()
    {
        return $this->hasMany(Dialog::class, 'advert_id', 'id');
    }

    /** Скоп активности объявления */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Скоп связи с юзером */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /** Скоп связи с категорией */
    public function scopeForCategory(Builder $query, Category $category)
    {
        return $query->whereIn('category_id', array_merge(
            [$category->id],
            $category->descendants()->pluck('id')->toArray()
        ));
    }

    /** Скоп связи с регионом */
    public function scopeForRegion(Builder $query, Region $region)
    {
        $ids = [$region->id];
        $childrenIds = $ids;
        while ($childrenIds = Region::where(['parent_id' => $childrenIds])->pluck('id')->toArray()) {
            $ids = array_merge($ids, $childrenIds);
        }
        return $query->whereIn('region_id', $ids);
    }

    /** Скоп связи избранного у пользователя */
    public function scopeFavoredByUser(Builder $query, User $user)
    {
        return $query->whereHas('favorites', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }
}
