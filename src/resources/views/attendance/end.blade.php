@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
  
  <!-- 状態ラベル -->
  <p class="status end">退勤済</p>

  <!-- 日付 -->
  <p class="date">{{ now()->format('Y年n月j日(D)') }}</p>

  <!-- 現在時刻 -->
  <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>

  <!-- メッセージ -->
  <p class="message">お疲れ様でした。</p>

</div>
@endsection
