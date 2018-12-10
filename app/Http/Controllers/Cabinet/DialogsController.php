<?php

namespace App\Http\Controllers\Cabinet;


use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Advert\Dialog\Dialog;
use App\Http\Controllers\Controller;
use App\UseCases\Adverts\DialogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Контроллер диалогов в личном кабинете пользователя */
class DialogsController extends Controller
{
    private $service;

    /** Принимаем сервис диалогов */
    public function __construct(DialogService $service)
    {
        $this->service = $service;
    }

    /** Отображаем на странице сообщений все сообщения связанные с пользователем */
    public function index()
    {
        $dialogs = Dialog::forUser(Auth::user())->orderByDesc('updated_at')->paginate(20);

        $user = Auth::user();

        return view('cabinet.messages.index', compact('dialogs', 'user'));
    }

    /** Выводим диалог пользователя */
    public function show(Dialog $dialog, Advert $advert)
    {
        $this->checkAccess($dialog);
        try {
        $this->service->read($dialog->id, $advert);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('cabinet.messages.show', compact('advert', 'dialog'));
    }

    /** ОТправляем новое сообщение в диалог */
    public function send(Dialog $dialog, Request $request)
    {

        $this->checkAccess($dialog);

        $this->validate($request, [
            'message' => 'required|string',
        ]);

        $clientId = Auth::id();

        try {
        $this->service->write($clientId, $request['message'], $dialog);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.messages.show', $dialog)->with('success', 'Ваше сообщение успешно отправлено!');
    }

    /** Проверка доступа к диалогу */
    private function checkAccess(Dialog $dialog): void
    {
        if (!Gate::allows('read-own-advert-dialog', $dialog)) {
            abort(404);
        }
    }
}