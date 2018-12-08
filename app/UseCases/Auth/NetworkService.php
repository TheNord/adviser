<?php

namespace App\UseCases\Auth;

use App\Entity\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as NetworkUser;

class NetworkService
{
    public function auth(string $network, NetworkUser $data): User
    {
        // ищем пользователя для авторизации через сеть
        if ($user = User::byNetwork($network, $data->getId())->first()) {
            return $user;
        }

        // из данных сети получаем email, ищем среди пользователей такой же email
        // отклоняем авторизацию, используется для безопасности и защиты от подделки email
        // со стороны сервиса авторизации
        if ($data->getEmail() && $user = User::where('email', $data->getEmail())->exists()) {
            throw new \DomainException('User with this email is already registered.');
        }

        // если не нашли пользователя, то регистрируем его
        // для надежности оборачиваем в транзакцию
        $user = DB::transaction(function () use ($network, $data) {
            return User::registerByNetwork($network, $data->getId());
        });

        // событие регистрации
        event(new Registered($user));

        return $user;
    }
}