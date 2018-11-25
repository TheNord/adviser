<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        session()->put('active', 'users');

        $query = User::orderByDesc('id');

        if (!empty($value = $request->get('id'))) {
            $query->where('id', $value);
        }

        if (!empty($value = $request->get('name'))) {
            $query->where('name', 'like', '%' . $value . '%');
        }

        if (!empty($value = $request->get('email'))) {
            $query->where('email', 'like', '%' . $value . '%');
        }

        if (!empty($value = $request->get('status'))) {
            $query->where('status', $value);
        }

        if (!empty($value = $request->get('role'))) {
            $query->where('role', $value);
        }

        $users = $query->paginate(20);

        $roles = [
            User::ROLE_USER => 'User',
            User::ROLE_ADMIN => 'Admin',
        ];

        $statuses = [
            User::STATUS_WAIT => 'Waiting',
            User::STATUS_ACTIVE => 'Active',
        ];


        return view('admin.users.index', compact('users', 'roles', 'statuses'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(CreateRequest $request)
    {
        $user = User::new(
            $request['name'],
            $request['email'],
            $request['password']
        );

        return redirect()->route('admin.users.show', $user);
    }

    public function show(User $user)
    {

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = [
          User::ROLE_USER => 'User',
          User::ROLE_ADMIN => 'Admin',
        ];

        $statuses = [
          User::STATUS_WAIT => 'Waiting',
          User::STATUS_ACTIVE => 'Active',
        ];

        return view('admin.users.edit', compact('user', 'roles', 'statuses'));
    }

    public function update(UpdateRequest $request, User $user)
    {
        $user->update([
            'name' => $request['name'],
            'email' => $request['email'],
        ]);

        if ($request['role'] !== $user->role) {
            $user->changeRole($request['role']);
        }

        if ($request['password'] != null) {
            $user->password = Hash::make($request['password']);
            $user->save();
        }

        return redirect()->route('admin.users.show', $user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index');
    }

    public function verify(User $user)
    {
        $user->verify();

        return redirect()->route('admin.users.show', $user);
    }
}
