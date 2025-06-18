<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <script src="https://kit.fontawesome.com/42694f25bf.js" crossorigin="anonymous"></script>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="{{ asset('/css/reset.css')  }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/common.css')  }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    @yield('css')
</head>

<body>
<header class="header">
  <a class="header__logo" href="/attendance">
    <img src="{{ asset('storage/images/logo.svg') }}" alt="COACHTECH">
  </a>
  <nav class="header__nav">
    <ul>
    @if (Auth::check())
    @php
        $user = Auth::user();
    @endphp

    @if ($user->role === 'admin')
        {{-- 管理者用ナビゲーション --}}
        <li><a class="header-nav__link" href="/admin/attendance/list">勤怠一覧</a></li>
        <li><a class="header-nav__link" href="/admin/staff_list">スタッフ一覧</a></li>
        <li><a class="header-nav__link" href="/stamp_correction_request/list">申請一覧</a></li>
        <li>
            <form action="/logout" method="post">
                @csrf
                <button class="header-nav__button">ログアウト</button>
            </form>
        </li>
    @else
        @php
            $routeName = Route::currentRouteName();
        @endphp

        @if ($routeName === 'attendance.end.screen')
            {{-- 退勤後のナビゲーション --}}
            <li><a class="header-nav__link" href="/attendance/list">今月の出勤一覧</a></li>
            <li><a class="header-nav__link" href="/application">申請一覧</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header-nav__button">ログアウト</button>
                </form>
            </li>
        @else
            {{-- 一般ユーザー用ナビゲーション --}}
            <li><a class="header-nav__link" href="/attendance">勤怠</a></li>
            <li><a class="header-nav__link" href="/attendance/list">勤怠一覧</a></li>
            <li><a class="header-nav__link" href="/request/list">申請</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header-nav__button">ログアウト</button>
                </form>
            </li>
        @endif
    @endif
    @endif

    </ul>
  </nav>
</header>

    @yield('content')
</body>

</html>