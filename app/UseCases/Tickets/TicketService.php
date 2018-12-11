<?php

namespace App\UseCases\Tickets;

use App\Entity\Adverts\Advert\Dialog\Dialog;
use App\Entity\Ticket\Message;
use App\Entity\Ticket\Ticket;
use App\Http\Requests\Ticket\CreateRequest;
use App\Http\Requests\Ticket\EditRequest;
use App\Http\Requests\Ticket\MessageRequest;

class TicketService
{
    /** Создание нового тикета */
    public function create(int $userId, CreateRequest $request): Ticket
    {
        return Ticket::new($userId, $request['subject'], $request['content']);
    }

    /** Создание нового тикета - жалобы */
    public function claim(int $userId, Dialog $dialog): Ticket
    {
        $subject = 'Жалоба';
        $content = 'Жалоба на диалог: ' . $dialog->id;

        return Ticket::new($userId, $subject, $content);
    }

    /** Редактирование тикета */
    public function edit(int $id, EditRequest $request): void
    {
        $ticket = $this->getTicket($id);
        $ticket->edit(
            $request['subject'],
            $request['content']
        );
    }

    /** Добавление нового сообщения */
    public function message(int $userId, int $id, MessageRequest $request): void
    {
        $ticket = $this->getTicket($id);
        $ticket->addMessage($userId, $request['message']);
    }

    /** Принятие тикета */
    public function approve(int $userId, int $id): void
    {
        $ticket = $this->getTicket($id);
        $ticket->approve($userId);
    }

    /** Отклонение тикета */
    public function close(int $userId, int $id): void
    {
        $ticket = $this->getTicket($id);
        $ticket->close($userId);
    }

    /** Переоткрытие тикета */
    public function reopen(int $userId, int $id): void
    {
        $ticket = $this->getTicket($id);
        $ticket->reopen($userId);
    }

    /** Удаление от владельца (пользователя) */
    public function removeByOwner(int $id): void
    {
        $ticket = $this->getTicket($id);
        if (!$ticket->canBeRemoved()) {
            throw new \DomainException('Unable to remove active ticket');
        }
        $ticket->delete();
    }

    /** Удаление от администратора */
    public function removeByAdmin(int $id): void
    {
        $ticket = $this->getTicket($id);
        $ticket->delete();
    }

    /** Удаление сообщения */
    public function removeMessage(int $id): void
    {
        $ticket = $this->getMessage($id);
        $ticket->delete();
    }

    /** Получение тикета по ид */
    private function getTicket($id): Ticket
    {
        return Ticket::findOrFail($id);
    }

    /** Получение сообщения по ид */
    private function getMessage($id): Message
    {
        return Message::findOrFail($id);
    }
}