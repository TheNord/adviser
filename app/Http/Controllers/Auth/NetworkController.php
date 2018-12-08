<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\UseCases\Auth\NetworkService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class NetworkController extends Controller
{
    private $service;

    public function __construct(NetworkService $service)
    {
        $this->service = $service;
    }

    public function redirect(string $network)
    {
        // редиректим на страницу авторизации
        return Socialite::driver($network)->redirect();
    }

    public function callback(string $network)
    {
        // получаем данные пользователя
        $data = Socialite::driver($network)->user();
        try {
            // получаем пользователя
            // если есть в базе то используем, если нет то регистрируем
            $user = $this->service->auth($network, $data);

            // авторизовываем полученного пользователя
            Auth::login($user);

            // возвращаяем на предыдущую страницу
            return redirect()->intended();
        } catch (\DomainException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }
}