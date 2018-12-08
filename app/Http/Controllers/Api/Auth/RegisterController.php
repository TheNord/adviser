<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Controllers\Controller;
use App\UseCases\Auth\RegisterService;
use Illuminate\Http\Response;

/** Регистрация по API */
class RegisterController extends Controller
{
    private $service;

    /** Принимаем сервис авторизации */
    public function __construct(RegisterService $service)
    {
        $this->service = $service;
    }

    /** Метод регистрации пользователя через API, POST
     *
     * @SWG\Post(
     *     path="/register",
     *     tags={"Profile"},
     *     @SWG\Parameter(name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/RegisterRequest")),
     *     @SWG\Response(
     *         response=201,
     *         description="Success response",
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $this->service->register($request);

        // возвращаяем ответ json'ом
        // HTTP_CREATED, возвращает код 201 об успешном создании
        return response()->json([
            'success' => 'Check your email and click on the link to verify.'
        ], Response::HTTP_CREATED);
    }
}