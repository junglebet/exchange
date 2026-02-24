<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        if($request->get('dashboard', false)) {
            return Inertia::location(route('admin.dashboard'));
        }

        if($request->wantsJson()) {
            $token = $request->user()->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        return Inertia::location(config('fortify.home'));
    }
}
