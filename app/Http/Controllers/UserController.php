<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('role')
            ->latest()
            ->paginate(12);

        $activity = ActivityLog::query()
            ->with('user')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('users.index', compact('users', 'activity'));
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'active' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
            'active' => $request->boolean('active'),
            'email_verified_at' => now(),
        ]);

        $logger->log(
            'user.created',
            'Se registró un usuario.',
            $user,
            [],
            $request->user()
        );

        return redirect()->route('users.index')->with('success', 'Usuario registrado.');
    }

    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(
        Request $request,
        User $user,
        ActivityLogger $logger
    ): RedirectResponse {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'role_id' => $data['role_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'active' => $request->boolean('active'),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        $logger->log(
            'user.updated',
            'Se actualizó un usuario.',
            $user,
            [],
            $request->user()
        );

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('warning', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
