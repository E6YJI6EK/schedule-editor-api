<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->userService->register($request->validated());

        return successResponse($user, 'Регистрация успешна', 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return errorResponse('Неверные учетные данные', 401);
        }

        $request->session()->regenerate();

        return successResponse($request->user(), 'Вход выполнен', 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return successResponse(null, 'Выход выполнен', 200);
    }

    public function me(Request $request)
    {
        return successResponse($request->user(), 'OK', 200);
    }

    public function employees()
    {
        return successResponse($this->userService->employees(), 'OK', 200);
    }

    public function deleteEmployee(int $id)
    {
        $error = $this->userService->deleteEmployee($id);

        if ($error === 'not_found') {
            return errorResponse('Сотрудник не найден', 404);
        }

        if ($error === 'forbidden') {
            return errorResponse('Нельзя удалить администратора', 403);
        }

        return successResponse(null, 'Сотрудник удалён', 200);
    }
}
