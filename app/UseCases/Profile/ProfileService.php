<?php

namespace App\UseCases\Profile;

use App\Entity\User;
use App\Http\Requests\Auth\ProfileEditRequest;

class ProfileService
{
    public function edit($id, ProfileEditRequest $request): void
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        // сохраняем старый телефон для сравнения
        $oldPhone = $user->phone;

        // обновляем данные
        $user->update($request->only('name', 'last_name', 'phone'));

        // если телефон изменился то снимаем верификацию с телефона
        if ($user->phone !== $oldPhone) {
            $user->unverifyPhone();
        }
    }
}