<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UsersController extends Controller
{
    public function show(User $user): User
    {
        return $user;
    }

    public function store(CreateUserRequest $request): User
    {
        $data = array_merge($request->validated(), ['bonus' => random_int(5, 20) / 100]);

        return User::create($data)->fresh();
    }

    public function update(User $user, UpdateUserRequest $request): User
    {
        $user->update($request->validated());

        return $user;
    }
}
