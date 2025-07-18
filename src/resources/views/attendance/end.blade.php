@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
<div class="attendance__content">
  
  <span class="attendance__status">退勤済</span>

  <div class="attendance__date">{{ now()->format('Y年n月j日(D)') }}</div>

  <div class="attendance__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

  <p class="message">お疲れ様でした。</p>
</div>
</div>
@endsection
