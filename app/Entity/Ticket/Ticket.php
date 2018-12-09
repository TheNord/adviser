<?php

namespace App\Entity\Ticket;

use App\Entity\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $subject
 * @property string $content
 * @property string $status
 *
 * @method Builder forUser(User $user)
 */
class Ticket extends Model
{
    protected $table = 'ticket_tickets';

    protected $guarded = ['id'];

    /** Создание нового тикета */
    public static function new(int $userId, string $subject, string $content): self
    {
        $ticket = self::create([
            'user_id' => $userId,
            'subject' => $subject,
            'content' => $content,
            'status' => Status::OPEN,
        ]);
        // добавляем в историю статус открытия
        $ticket->setStatus(Status::OPEN, $userId);

        return $ticket;
    }

    /** Редактирование тикета */
    public function edit(string $subject, string $content): void
    {
        $this->update([
            'subject' => $subject,
            'content' => $content,
        ]);
    }

    /** Добавление нового сообщения */
    public function addMessage(int $userId, $message): void
    {
        if (!$this->allowsMessages()) {
            throw new \DomainException('Ticket is closed for messages.');
        }

        // добавление нового сообщения через связь messages (ticket_id = id)
        $this->messages()->create([
            'user_id' => $userId,
            'message' => $message,
        ]);

        $this->update();
    }

    /** Проверка на доступ к добавлению сообщения */
    public function allowsMessages(): bool
    {
        return !$this->isClosed();
    }

    /** Изменение статуса на: Принят */
    public function approve(int $userId): void
    {
        if ($this->isApproved()) {
            throw new \DomainException('Ticket is already approved.');
        }
        $this->setStatus(Status::APPROVED, $userId);
    }

    /** Изменение статуса на: Закрыт */
    public function close(int $userId): void
    {
        if ($this->isClosed()) {
            throw new \DomainException('Ticket is already closed.');
        }
        $this->setStatus(Status::CLOSED, $userId);
    }

    /** Переоткрытие тикета */
    public function reopen(int $userId): void
    {
        if (!$this->isClosed()) {
            throw new \DomainException('Ticket is not closed.');
        }
        $this->setStatus(Status::APPROVED, $userId);
    }

    /** Проверка статуса: Открыт */
    public function isOpen(): bool
    {
        return $this->status === Status::OPEN;
    }

    /** Проверка статуса: Принят */
    public function isApproved(): bool
    {
        return $this->status === Status::APPROVED;
    }

    /** Проверка статуса: Закрыт */
    public function isClosed(): bool
    {
        return $this->status === Status::CLOSED;
    }

    /** Проверка на возможность удаления тикета */
    public function canBeRemoved(): bool
    {
        return $this->isOpen();
    }

    /** Связь с пользователем, для получения информации о пользователе */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /** Связь с сообщениями в тикете */
    public function messages()
    {
        return $this->hasMany(Message::class, 'ticket_id', 'id');
    }

    /** Связь с историей статусов */
    public function statuses()
    {
        return $this->hasMany(Status::class, 'ticket_id', 'id');
    }

    /** Изменение статуса с сохранением в историю статусов */
    private function setStatus($status, ?int $userId): void
    {
        $this->statuses()->create(['status' => $status, 'user_id' => $userId]);
        $this->update(['status' => $status]);
    }

    /** Скоп для вывода только тех тикетов которые принадлежат пользователю */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}