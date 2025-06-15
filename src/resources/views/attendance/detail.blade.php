@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <div class="attendance-detail-box">
        <h2 class="attendance-title">勤怠詳細</h2>

        <form action="{{ route('attendance.request', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                    ～
                    <input type="time" name="clock_out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                </td>
            </tr>

            @foreach($attendance->breaks as $index => $break)
            <tr>
            <th>休憩{{ $index + 1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $break->id }}][start]" value="{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '' }}">
                    ～
                    <input type="time" name="breaks[{{ $break->id }}][end]" value="{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '' }}">
                </td>
            </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td><input type="text" name="note" value="{{ $attendance->note }}" /></td>
            </tr>
        </table>

        <div class="attendance-detail-button">
            <button type="submit">修正</button>
        </div>
    </div>
</div>
@endsection
