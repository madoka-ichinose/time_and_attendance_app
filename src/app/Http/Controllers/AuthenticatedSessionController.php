<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): LoginResponse
    {
    // 認証済みだがメール未認証のユーザーにリダイレクト
    if (! $request->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    // 通常ログイン処理（例）
    return app(LoginResponse::class);
    }
}
