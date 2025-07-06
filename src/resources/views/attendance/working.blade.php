@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
  <div class="attendance__content">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
 
  <span class="attendance__status">出勤中</span>
  <div class="attendance__date">{{ now()->format('Y年n月j日(D)') }}</div>
  <div class="attendance__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>
  <div class="btn-group">
    <form action="{{ route('attendance.end') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-end">退勤</button>
    </form>
    <form action="{{ route('attendance.break') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-break">休憩入</button>
    </form>
  </div>
  </div>
</div>
@endsection
