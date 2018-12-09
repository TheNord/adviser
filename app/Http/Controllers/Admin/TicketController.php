<?php

namespace App\Http\Controllers\Admin;

use App\Entity\Ticket\Message;
use App\Entity\Ticket\Status;
use App\Entity\Ticket\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\EditRequest;
use App\Http\Requests\Ticket\MessageRequest;
use App\UseCases\Tickets\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    private $service;

    /** Подключение сервиса тикетов и проверка на управление тикетами */
    public function __construct(TicketService $service)
    {
        $this->service = $service;
        $this->middleware('can:manage-tickets');
    }

    /** Вывод списка всех тикетов */
    public function index(Request $request)
    {
        $query = Ticket::orderByDesc('updated_at');

        /** Фильтрация тикетов */
        if (!empty($value = $request->get('id'))) {
            $query->where('id', $value);
        }

        if (!empty($value = $request->get('user'))) {
            $query->where('user_id', $value);
        }

        if (!empty($value = $request->get('status'))) {
            $query->where('status', $value);
        }

        // пагинация полученных тикетов
        $tickets = $query->paginate(20);
        // список доступных статусов
        $statuses = Status::statusesList();

        return view('admin.tickets.index', compact('tickets', 'statuses'));
    }

    /** Страница просмотра тикета */
    public function show(Ticket $ticket)
    {
        return view('admin.tickets.show', compact('ticket'));
    }

    /** Форма редактирования тикета */
    public function editForm(Ticket $ticket)
    {
        return view('admin.tickets.edit', compact('ticket'));
    }

    /** Редактирование тикета */
    public function edit(EditRequest $request, Ticket $ticket)
    {
        try {
            $this->service->edit($ticket->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }

    /** Добавление сообщения к тикету */
    public function message(MessageRequest $request, Ticket $ticket)
    {
        try {
            $this->service->message(Auth::id(), $ticket->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }

    /** Принятие тикета */
    public function approve(Ticket $ticket)
    {
        try {
            $this->service->approve(Auth::id(), $ticket->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }

    /** Закрытие тикета */
    public function close(Ticket $ticket)
    {
        try {
            $this->service->close(Auth::id(), $ticket->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }

    /** Переоткрытие тикета */
    public function reopen(Ticket $ticket)
    {
        try {
            $this->service->reopen(Auth::id(), $ticket->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }

    /** Удаление тикета */
    public function destroy(Ticket $ticket)
    {
        try {
            $this->service->removeByAdmin($ticket->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.index');
    }

    /** Удаление сообщения в тикетах */
    public function messageDestroy(Ticket $ticket, Message $message)
    {
        try {
            $this->service->removeMessage($message->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('admin.tickets.show', $ticket);
    }
}