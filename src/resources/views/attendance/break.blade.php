@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
<div class="attendance__content">
  
  <!-- 状態ラベル -->
  <span class="attendance__status">休憩中</span>

  <!-- 日付 -->
  <div class="attendance__date">{{ now()->format('Y年n月j日(D)') }}</div>

  <!-- 現在時刻 -->
  <div class="attendance__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

  <!-- 休憩戻ボタン -->
  <div class="btn-group">
    <form action="{{ route('attendance.break.return') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-return">休憩戻</button>
    </form>
  </div>
</div>
</div>
@endsection
