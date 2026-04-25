<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => $request->string('password'),
            'role' => Role::Employee,
        ]);

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

    public function deleteEmployee(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return errorResponse('Сотрудник не найден', 404);
        }

        if ($user->role === Role::Admin) {
            return errorResponse('Нельзя удалить администратора', 403);
        }

        $user->delete();

        return successResponse(null, 'Сотрудник удалён', 200);
    }
}
