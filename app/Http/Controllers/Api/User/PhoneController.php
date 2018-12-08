<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PhoneVerifyRequest;
use App\UseCases\Profile\PhoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneController extends Controller
{
    private $service;

    public function __construct(PhoneService $service)
    {
        $this->service = $service;
    }

    /** Запрос смс кода */
    public function request(Request $request)
    {
        $response = $this->service->request($request->user()->id);

        return $response;
    }

    /** Проверка полученного из формы кода и условия по времени */
    public function verify(PhoneVerifyRequest $request)
    {
        try {
            $this->service->verify(Auth::id(), $request);
        } catch (\DomainException $e) {
            return redirect()->route('cabinet.profile.phone')->with('error', $e->getMessage());
        }

        return redirect()->route('cabinet.profile.home');
    }

    /** Переключение статуса двухфакторной авторизации */
    public function auth()
    {
        $this->service->toggleAuth(Auth::id());

        return redirect()->route('cabinet.profile.home');
    }
}