<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => ['required', Rule::in(['system_admin', 'admin', 'user'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in(['system_admin', 'admin', 'user'])],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->withErrors(['error' => 'You cannot delete yourself!']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
