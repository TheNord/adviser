<?php

namespace App\Http\Controllers\Cabinet;

use App\Entity\Ticket\Ticket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateRequest;
use App\Http\Requests\Ticket\MessageRequest;
use App\UseCases\Tickets\TicketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    private $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
    }

    /** Вывод тикетов для текущего пользователя с сортировкой по последнему обновлению */
    public function index()
    {
        $tickets = Ticket::forUser(Auth::user())->orderByDesc('updated_at')->paginate(20);

        return view('cabinet.tickets.index', compact('tickets'));
    }

    /** Страница просмотра тикета */
    public function show(Ticket $ticket)
    {
        return view('cabinet.tickets.show', compact('ticket'));
    }

    /** Страница создания нового тикета */
    public function create()
    {
        return view('cabinet.tickets.create');
    }

    /** Создание нового тикета */
    public function store(CreateRequest $request)
    {
        try {
            $ticket = $this->service->create(Auth::id(), $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('cabinet.tickets.show', $ticket);
    }

    /** Отправка сообщения */
    public function message(MessageRequest $request, Ticket $ticket)
    {
        $this->checkAccess($ticket);
        try {
            $this->service->message(Auth::id(), $ticket->id, $request);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('cabinet.tickets.show', $ticket);
    }

    /** Удаление тикета */
    public function destroy(Ticket $ticket)
    {
        $this->checkAccess($ticket);
        try {
            $this->service->removeByOwner($ticket->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('cabinet.favorites.index');
    }

    /** Проверка доступа к управлению тикета */
    private function checkAccess(Ticket $ticket): void
    {
        if (!Gate::allows('manage-own-ticket', $ticket)) {
            abort(403);
        }
    }
}