<?php

namespace App\Http\Controllers\Adverts;

use App\Entity\Adverts\Advert\Advert;
use App\Entity\Adverts\Advert\Dialog\Dialog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Контроллер начала диалога в объявлении */
class DialogsController extends Controller
{
    /** Отображаем или создаем диалог */
    public function show(Advert $advert)
    {
        $client_id = Auth::id();

        try {
            // находим либо создаем новый диалог
            $dialog = $advert->getOrCreateDialogWith($client_id);
            // читаем сообщение от имени написавшего пользователя
            $advert->readOwnerMessages($dialog->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return view('adverts.messages.show', compact('advert', 'dialog'));
    }

    public function send(Advert $advert, Dialog $dialog, Request $request)
    {
        $this->validate($request, [
            'message' => 'required|string',
        ]);

        $client_id = Auth::id();

        try {
            $advert->writeClientMessage($client_id, $request['message']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('adverts.message.dialog', $advert)->with('success', 'Ваше сообщение успешно отправлено!');
    }

}