<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): LoginResponse
    {
    if (! $request->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return app(LoginResponse::class);
    }
}
