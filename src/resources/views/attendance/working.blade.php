@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
 
  <p class="status">出勤中</p>
  <p class="date">{{ now()->format('Y年n月j日(D)') }}</p>
  <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
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
@endsection
