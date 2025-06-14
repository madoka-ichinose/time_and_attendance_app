@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
  
  <!-- 状態ラベル -->
  <p class="status rest">休憩中</p>

  <!-- 日付 -->
  <p class="date">{{ now()->format('Y年n月j日(D)') }}</p>

  <!-- 現在時刻 -->
  <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>

  <!-- 休憩戻ボタン -->
  <div class="btn-group">
    <form action="{{ route('attendance.break.return') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-return">休憩戻</button>
    </form>
  </div>

</div>
@endsection
