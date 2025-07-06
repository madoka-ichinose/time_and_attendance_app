@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
<div class="attendance__content">
  
  <!-- 状態ラベル -->
  <span class="attendance__status">退勤済</span>

  <!-- 日付 -->
  <div class="attendance__date">{{ now()->format('Y年n月j日(D)') }}</div>

  <!-- 現在時刻 -->
  <div class="attendance__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

  <!-- メッセージ -->
  <p class="message">お疲れ様でした。</p>
</div>
</div>
@endsection
