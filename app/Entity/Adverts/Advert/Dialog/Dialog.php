<?php

namespace App\Entity\Adverts\Advert\Dialog;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property int $advert_id
 * @property int $user_id
 * @property int $client_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_new_messages
 * @property int $client_new_messages
 */
class Dialog extends Model
{
    protected $table = 'advert_dialogs';

    protected $guarded = ['id'];

    /** Написать сообщение от владельца объявления */
    public function writeMessageByOwner(int $userId, string $message): void
    {
        $this->messages()->create([
            'user_id' => $userId,
            'message' => $message,
        ]);

        // Добавляем клиенту число непрочитанных объявлений
        $this->client_new_messages++;

        $this->save();
    }

    /** Написать сообщение от клиента */
    public function writeMessageByClient(int $userId, string $message): void
    {
        $this->messages()->create([
            'user_id' => $userId,
            'message' => $message,
        ]);

        // добавляем автору число непрочитанных объявлений
        $this->user_new_messages++;

        $this->save();
    }

    /** После прочтения обнуляем счетчик новых сообщения у автора*/
    public function readByOwner(): void
    {
        $this->update(['user_new_messages' => 0]);
    }

    /** После прочтения обнуляем счетчик новых сообщения у клиента */
    public function readByClient(): void
    {
        $this->update(['client_new_messages' => 0]);
    }

    /** Связь с объявлением */
    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id', 'id');
    }

    /** Связь с пользователем */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    /** Связь с сообщениями */
    public function messages()
    {
        return $this->hasMany(Message::class, 'dialog_id', 'id');
    }

    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('user_id', $user->id)->orWhere('client_id', $user->id);
    }
}