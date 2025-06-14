@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<title>メール認証</title>

<div class="verify-container">
    <div class="verify-box">
        <p>登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。</p>

        <a href="http://localhost:8025" class="verify-button" target="_blank" rel="noopener noreferrer">
            認証はこちらから
        </a>

        @if (session('status') == 'verification-link-sent')
            <p>認証メールを再送しました。</p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" id="resend-form">
        @csrf
            <a href="#" class="resend-link" onclick="document.getElementById('resend-form').submit(); return false;">
            認証メールを再送する
            </a>
        </form>
    </div>
</div>
@endsection
