<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => Role::Employee,
        ]);
    }

    public function employees(): Collection
    {
        return User::where('role', Role::Employee)->get();
    }

    public function deleteEmployee(int $id): string|null
    {
        $user = User::find($id);

        if (!$user) {
            return 'not_found';
        }

        if ($user->role === Role::Admin) {
            return 'forbidden';
        }

        $user->delete();

        return null;
    }
}
