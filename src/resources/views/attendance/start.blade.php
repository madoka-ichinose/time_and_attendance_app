@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__content">
        <span class="attendance__status">勤務外</span>
        <div class="attendance__date">{{ \Carbon\Carbon::now()->format('Y年n月j日(D)') }}</div>
        <div class="attendance__time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

        <form method="POST" action="{{ route('attendance.start') }}">
            @csrf
            <button type="submit" id="clock-in-button"  class="attendance__button">出勤</button>
        </form>
    </div>
</div>
@endsection