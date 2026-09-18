<?php

namespace App\Http\Controllers;

use App\Models\Greenhouse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->with('role')->where('email', $data['email'])->first();

        if (! $user || ! $user->active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        $token = $user->createToken('lumatek-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
            'greenhouses' => $this->greenhousesFor($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'user' => $this->userPayload($user),
            'greenhouses' => $this->greenhousesFor($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión móvil cerrada.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role?->slug,
        ];
    }

    private function greenhousesFor(User $user): array
    {
        return Greenhouse::query()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('responsible_user_id', $user->id))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'location', 'crop_type', 'status'])
            ->toArray();
    }
}